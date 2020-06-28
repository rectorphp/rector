<?php

namespace Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\Fixture;

final class ExtraClassName
{
    /**
     * @var string
     */
    public const VALUE = 'value';
    public function newMethodName()
    {
        return self::VALUE;
    }
}
