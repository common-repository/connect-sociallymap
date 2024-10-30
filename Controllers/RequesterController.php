<?php

namespace SociallymapConnect\Controllers;

use SociallymapConnect\Configs\System\FunctionsConfig;

class RequesterController extends BaseController
{
    /**
     * @return bool
     * @throws \ReflectionException
     */
    public static function addNoticeDisabledFunctions()
    {
        $disabledFunction = self::getNecessaryButDisabledFunctions();

        $needNotice = (in_array('file_get_contents', $disabledFunction, true) && count($disabledFunction) > 2);

        return $needNotice;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getNecessaryButDisabledFunctions()
    {
        $necessariesFunctions = FunctionsConfig::getValues();

        $disabledFunctions = explode(',', ini_get('disable_functions'));

        $necessaryButDisabledFunctions = [];
        foreach ($necessariesFunctions as $functionName) {
            if (in_array($functionName, $disabledFunctions, true)) {
                $necessaryButDisabledFunctions[] = $functionName;
            }
        }

        return $necessaryButDisabledFunctions;
    }
}
