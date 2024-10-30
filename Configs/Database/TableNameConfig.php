<?php

namespace SociallymapConnect\Configs\Database;

use SociallymapConnect\Enums\SmartEnum;

class TableNameConfig extends SmartEnum
{
    const LOG = 'smc_logs';
    const ENTITIES = 'smc_entities';
    const CATEGORIES = 'smc_entities_categories';
    const PUBLISHED = 'smc_message_published';
    const OPTIONS = 'smc_request-secure';

    const SMC_POST_META = '_smc_canonical_link';
}
