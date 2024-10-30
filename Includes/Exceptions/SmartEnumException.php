<?php

namespace SociallymapConnect\Includes\Exceptions;

class SmartEnumException extends BasePluginException
{
    public function __construct($value)
    {
        $message = sprintf('Invalid Value "%s".', (string)$value);

        parent::__construct($message, 500);
    }
}
