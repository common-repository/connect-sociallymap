<?php

namespace SociallymapConnect\Enums\Publication;

use SociallymapConnect\Enums\SmartEnum;

class Type extends SmartEnum
{
    const PUBLISH = 'publish';
    const DRAFT = 'draft';
    const PENDING = 'pending';
    const PRIVATE_POST = 'private';
}
