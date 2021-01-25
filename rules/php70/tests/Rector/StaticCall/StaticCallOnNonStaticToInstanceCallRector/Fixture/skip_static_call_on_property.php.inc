<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\WithOnlyStaticMethods;

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
