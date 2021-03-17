<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use DateTime as PhpDateTime;
use DateTimeImmutable as PhpDateTimeImmutable;

class SkipDocBlockUniontype
{
    /**
     * @var PhpDateTime|bool
     */
    protected $b;

    /**
     * @var PhpDateTimeImmutable
     */
    protected $a;
}
