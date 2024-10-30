<?php

namespace SociallymapConnect\Includes;

use SociallymapConnect\Includes\Exceptions\RequesterException;
use SociallymapConnect\Includes\HttpRequester\HttpResponse;

class RequesterCurlDriver extends BaseRequesterDriver
{

    protected function initDriverName()
    {
        $this->name = 'Curl';
    }

    private function formatResponse($response)
    {
        $separator = "\r\n\r\n";

        $headers = explode($separator, $response)[0];
        $body = explode($separator, $response)[1];


        preg_match('|HTTP/(\d\.\d)\s+(\d+)\s+(.*)|', $headers, $matches);

        $response = new HttpResponse();
        $response->setRawHeader($headers);
        $response->setHttpStatusCode($matches[2]);
        $response->setHttpStatusPhrase($matches[3]);
        $response->setRawBody($body);

        return $response;
    }

    /**
     * @param string $url
     * @return string
     * @throws Exceptions\SmartEnumException
     * @throws RequesterException
     * @throws \ReflectionException
     */
    public function download($url)
    {
        $filename = pathinfo($url, PATHINFO_BASENAME);
        $tempFolder = plugin_dir_path(__FILE__) . '/../tmp/';
        if (!file_exists($tempFolder) && !mkdir($tempFolder) && !is_dir($tempFolder)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
        }
        $curl = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        curl_setopt_array($curl, $options);

        try {
            $response = curl_exec($curl);
        } catch (\Exception $exception) {
            Logger::logError($exception->getMessage());
        }

        $response = $this->formatResponse($response);

        if ($response->getHttpStatusCode() === '403') {
            Logger::logError(sprintf('Status invalid -> %s', $response->getHttpStatusCode()));
        }

        $curl_errno = curl_errno($curl);
        $curl_error = curl_error($curl);

        if ($curl_errno) {
            $errorMessage = sprintf('Error #%s : %s', $curl_errno, $curl_error);
            throw new RequesterException($errorMessage);
        }

        $tempFilename = $tempFolder . $filename;
        \file_put_contents($tempFilename, $response->getRawBody());

        return $tempFilename;
    }

    /**
     * @param $url
     * @param bool $secureRequest
     * @return mixed
     * @throws \Exception
     */
    public function sendRequest($url, $secureRequest)
    {
        $curl = curl_init();

        $verifyHost = ($secureRequest) ? 2 : 0;

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => $verifyHost,
            CURLOPT_SSL_VERIFYPEER => (bool)$secureRequest,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ];

        curl_setopt_array($curl, $options);

        try {
            $response = curl_exec($curl);
        } catch (\Exception $exception) {
            Logger::logError($exception->getMessage());
        }

        $curlErrno = curl_errno($curl);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($curlErrno) {
            $errorMessage = sprintf('#%s : %s', $curlErrno, $curlError);
            throw new RequesterException($errorMessage);
        }

        return $this->formatResponse($response);
    }

}
