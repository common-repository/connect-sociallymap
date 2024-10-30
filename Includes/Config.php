<?php

namespace SociallymapConnect\Includes;

class Config
{
    /** @var array */
    protected $parameters = [];

    /**
     * @param array $configData
     */
    public function __construct(array $configData)
    {
        $this->parameters = $configData;
    }

    /**
     * @return string
     */
    public function getLogDriver()
    {
        return $this->parameters['logDriver'];
    }

}
