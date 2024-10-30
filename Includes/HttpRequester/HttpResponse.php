<?php

namespace SociallymapConnect\Includes\HttpRequester;

class HttpResponse
{
    const MAX_RAW_BODY_SIZE = 100E+3;

    /** @var string */
    private $rawResponse;

    /** @var string */
    private $rawRequest;

    /** @var string */
    private $rawHeader;

    /** @var string */
    private $rawBody;

    /** @var string */
    private $httpStatusCode;

    /** @var string */
    private $httpStatusPhrase;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @param string $rawResponse
     */
    public function setRawResponse($rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    /**
     * @return string
     */
    public function getRawRequest()
    {
        return $this->rawRequest;
    }

    /**
     * @param string $rawRequest
     */
    public function setRawRequest($rawRequest)
    {
        $this->rawRequest = $rawRequest;
    }

    /**
     * @return string
     */
    public function getRawHeader()
    {
        return $this->rawHeader;
    }

    /**
     * @param string $rawHeader
     */
    public function setRawHeader($rawHeader)
    {
        $this->rawHeader = $rawHeader;
    }

    /**
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * @param string $rawBody
     */
    public function setRawBody($rawBody)
    {
        $this->rawBody = $rawBody;
    }

    /**
     * @return string
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @param string $httpStatusCode
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * @return string
     */
    public function getHttpStatusPhrase()
    {
        return $this->httpStatusPhrase;
    }

    /**
     * @param string $httpStatusPhrase
     */
    public function setHttpStatusPhrase($httpStatusPhrase)
    {
        $this->httpStatusPhrase = $httpStatusPhrase;
    }
}
