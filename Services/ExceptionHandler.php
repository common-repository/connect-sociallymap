<?php

namespace SociallymapConnect\Services;

use SociallymapConnect\Includes\Exceptions\EntityDisabledException;
use SociallymapConnect\Includes\Exceptions\EntityNotFoundException;
use SociallymapConnect\Includes\Exceptions\LoggerException;
use SociallymapConnect\Includes\Exceptions\PostFailureException;
use SociallymapConnect\Includes\Exceptions\RequesterException;
use SociallymapConnect\Includes\Exceptions\SmartEnumException;
use SociallymapConnect\Includes\Exceptions\UnprocessableEntityException;
use SociallymapConnect\Includes\Logger;

class ExceptionHandler
{
    private static function addHeaders($error)
    {
        header('HTTP/1.0 ' . $error);
        header('Content-Type: application/json');
    }

    public static function handleException(\Exception $exception)
    {
        $exceptionClass = get_class($exception);
        Logger::logException($exceptionClass . ' : ' . $exception->getMessage());

        switch ($exceptionClass) {
            case RequesterException::class:
                self::addHeaders('503 Service Unavailable');
                break;
            case UnprocessableEntityException::class:
                Logger::logError('trace : ' . print_r(debug_backtrace(), true));
                self::addHeaders('422 Unprocessable entity');
                break;
            case LoggerException::class:
                self::addHeaders('500 Internal Server Error');
                break;
            case PostFailureException::class:
                self::addHeaders('422 Unprocessable entity');
                break;
            case EntityDisabledException::class:
                self::addHeaders('422 Unprocessable entity');
                break;
            case EntityNotFoundException::class:
                self::addHeaders('404 Not Found');
                break;
            case SmartEnumException::class:
                self::addHeaders('500 Internal Server Error');
                break;
            default:
                self::addHeaders('500 Internal Server Error');
                Logger::logError('Unknown error');
                Logger::logError('trace : ' . var_export(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5), true));
        }
        echo \json_encode(['error' => $exception->getMessage()]);
        exit();
    }
}
