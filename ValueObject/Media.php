<?php

namespace SociallymapConnect\ValueObject;

class Media
{
    /** @var int $id */
    protected $id;

    /** @var string $url */
    protected $url;

    /** @var string $type */
    protected $type;

    /** @var string $title */
    protected $title;

    public function __construct($id, $url, $type)
    {
        $this->id = $id;
        $this->url = $url;
        $this->type = $type;
        $this->title = preg_replace('/\.[^.]+$/', '', $url);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
