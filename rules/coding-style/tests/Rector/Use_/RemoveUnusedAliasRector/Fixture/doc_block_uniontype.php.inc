<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use DateTime as PhpDateTime;
use DateTimeImmutable as PhpDateTimeImmutable;

class DocBlockUniontype
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
