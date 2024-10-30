<?php

namespace SociallymapConnect\Services;

use SociallymapConnect\Includes\Errors\MessageAlreadyPublishedError;
use SociallymapConnect\Includes\Logger;

class ErrorHandler
{

    public static function handleException(\WP_Error $error)
    {
        $errorClass = get_class($error);
        Logger::logError($errorClass . ' : ' . $error->get_error_message());

        switch ($errorClass) {
            case MessageAlreadyPublishedError::class:
                Logger::logError($error->get_error_message());
                break;
            default:
                Logger::logError('Unknown error');
        }
    }
}
