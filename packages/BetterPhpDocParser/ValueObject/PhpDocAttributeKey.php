<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class PhpDocAttributeKey
{
    /**
     * @var string
     */
    public const START_AND_END = StartAndEnd::class;

    /**
     * @var string
     */
    public const PARENT = 'parent';
}
