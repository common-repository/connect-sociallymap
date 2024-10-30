<?php

namespace SociallymapConnect\Configs\SupportedPlugin;

use SociallymapConnect\Enums\SmartEnum;

class YoastConfig extends SmartEnum
{
    const FILE = 'wordpress-seo/wp-seo.php';
    const NAME = 'YoastConfig SEO';
    const POST_META = '_yoast_wpseo_canonical';
}
