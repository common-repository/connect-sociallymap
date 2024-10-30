<?php

namespace SociallymapConnect\Includes\Exceptions;

use SociallymapConnect\Configs\PluginConfig;

class EntityNotFoundException extends BasePluginException
{
    public function __construct($message = null, $code = 404, \Throwable $previous = null)
    {
        if ($message === null) {
            $message = __('L\'entité n\'a pas été trouvée', PluginConfig::DOMAIN_TRANSLATE);
        }
        parent::__construct($message, $code, $previous);
    }
}
