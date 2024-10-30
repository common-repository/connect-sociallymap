<?php

namespace SociallymapConnect\Includes;

use SociallymapConnect\Configs\UrlSociallymapConfig;

abstract class BaseRequesterDriver
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $environment;

    /**
     * @param string $environment
     */
    public function __construct($environment)
    {
        $this->environment = $environment;
        $this->baseUrl = constant(UrlSociallymapConfig::class . '::' . strtoupper($environment));
        $this->initDriverName();
    }

    protected function initDriverName()
    {

    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
