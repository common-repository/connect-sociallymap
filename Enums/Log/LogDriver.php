<?php

namespace SociallymapConnect\Enums\Log;

use SociallymapConnect\Enums\SmartEnum;

class LogDriver extends SmartEnum
{
    const FILE_SYSTEM = 'filesystem';
    const DATABASE = 'database';
    const NOLOG = 'nolog';
}
