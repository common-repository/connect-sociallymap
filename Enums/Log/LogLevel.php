<?php

namespace SociallymapConnect\Enums\Log;

use SociallymapConnect\Enums\SmartEnum;

class LogLevel extends SmartEnum
{
    const DUMP = 'dump';
    const ERROR = 'error';
    const EXCEPTION = 'exception';
    const INFO = 'info';
    const MESSAGE_RECEIVED = 'message_received';
}
