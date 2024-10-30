<?php

namespace SociallymapConnect\Configs;

use SociallymapConnect\Enums\SmartEnum;

class UrlSociallymapConfig extends SmartEnum
{
    const PROD = 'https://api.sociallymap.com';
    const PREPROD = 'https://api.sociallymap-staging.com';
    const DEV = 'https://api.sociallymap.vagrant';
    const TEST = 'https://sociallymap.test';
}
