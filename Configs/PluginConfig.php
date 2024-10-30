<?php

namespace SociallymapConnect\Configs;

use SociallymapConnect\Enums\SmartEnum;

class PluginConfig extends SmartEnum
{
    const NAME = 'Sociallymap Connect';
    const PATH = __DIR__ . '/../SociallymapConnectPlugin.php';
    const DOMAIN_TRANSLATE = 'sociallymap-connect';
}
