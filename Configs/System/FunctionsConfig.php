<?php

namespace SociallymapConnect\Configs\System;

use SociallymapConnect\Enums\SmartEnum;

class FunctionsConfig extends SmartEnum
{
    const CURL_INIT = 'curl_init';
    const CURL_EXEC = 'curl_exec';
    const CURL_SETOPT_ARRAY = 'curl_setopt_array';
    const FILE_GET_CONTENTS = 'file_get_contents';
}
