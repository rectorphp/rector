<?php

namespace Rector\Decouple\Tests\Rector\ClassMethod\DecoupleClassMethodToOwnClassRector\Fixture;

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
