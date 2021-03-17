<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

final class SkipStaticCallOnProperty
{
    /**
     * @var WithOnlyStaticMethods
     */
    private $withOnlyStaticMethods;

    public function __construct(WithOnlyStaticMethods $withOnlyStaticMethods)
    {
        $this->withOnlyStaticMethods = $withOnlyStaticMethods;
    }

    public function cast()
    {
        $this->withOnlyStaticMethods::aBoolMethod();
    }
}
