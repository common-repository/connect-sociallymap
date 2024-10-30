<?php

namespace SociallymapConnect\Includes;

use SociallymapConnect\Configs\System\RequiredVersionConfig;
use SociallymapConnect\Includes\Exceptions\EntityNotFoundException;
use SociallymapConnect\Includes\Exceptions\Error500Exception;
use SociallymapConnect\Includes\Exceptions\UnprocessableEntityException;
use SociallymapConnect\Includes\HttpRequester\HttpResponse;

class Requester
{
    /**
     * @var BaseRequesterDriver
     */
    protected $driver;

    /**
     * Requester constructor.
     * @param string        $environment
     * @throws Error500Exception
     */
    public function __construct($environment = 'dev')
    {
        $driver = $this->determineDriver();
        $this->driver = new $driver($environment);
    }

    /**
     * @return boolean
     */
    protected function curlAvailable()
    {
        $response = false;

        if (function_exists('curl_init')) {
            $curlVersion = curl_version();
            if (version_compare($curlVersion['version'], RequiredVersionConfig::CURL, '>=')) {
                $response = true;
            }
        }

        return $response;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function downloadFromDriver($url)
    {
        return $this->driver->download($url);
    }

    /**
     * @return boolean
     */
    protected function fileGetContentsAvailable()
    {
        return function_exists('file_get_contents');
    }

    /**
     * @param string $url
     * @param boolean $secureRequest
     * @return string
     */
    protected function sendRequest($url, $secureRequest)
    {
        return $this->driver->sendRequest($url, $secureRequest);
    }

    /**
     * @return string
     * @throws Error500Exception
     */
    public function determineDriver()
    {
        if ($this->curlAvailable()) {
            $driver = 'SociallymapConnect\Includes\RequesterCurlDriver';
        } else if ($this->fileGetContentsAvailable()) {
            $driver = 'SociallymapConnect\Includes\RequesterFileGetContentDriver';
        } else {
            $errorMessage = 'No driver available for processing Sociallymap messages.';
            throw new Error500Exception($errorMessage);
        }

        return $driver;
    }

    /**
     * @param string $url
     * @return string
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public function download($url)
    {
        Logger::logInfo(sprintf('Download file with %s : %s', $this->driver->getName(), $url));

        return $this->downloadFromDriver($url);
    }

    /**
     * @return BaseRequesterDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string  $entityId
     * @param string  $token
     * @param boolean $secureRequest
     * @return array
     * @throws \Exception
     */
    public function getMessages($entityId, $token, $secureRequest)
    {
        $messageSecureRequest = $secureRequest ? 'On' : 'Off';

        $url = sprintf('%s/raw-exporter/%s/feed?token=%s', $this->driver->getBaseUrl(), $entityId, $token);
        Logger::logInfo(sprintf('Call Sociallymap raw-exporter with %s (secure request : %s) : %s', $this->driver->getName(), $messageSecureRequest, $url));
        /** @var HttpResponse $response */
        $response = $this->sendRequest($url, $secureRequest);

        $this->checkResponse($response);

        Logger::logMessageReceived('Received message');
        return json_decode($response->getRawBody(), true);
    }

    private function checkResponse(HttpResponse $response)
    {
        if (!empty($response->getHttpStatusCode()) && $response->getHttpStatusCode() === '422') {
            throw new UnprocessableEntityException('Sociallymap response : ' . json_decode($response->getRawBody())->error);
        }
        if (!empty($response->getHttpStatusCode()) && $response->getHttpStatusCode() === '404') {
            throw new EntityNotFoundException('Sociallymap response : ' . json_decode($response->getRawBody())->error);
        }
    }
}
