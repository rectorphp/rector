<?php

namespace Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

class SkipPropertyCall
{
    /**
     * @var DummyValidator
     */
    private $dummyValidator;

    public function run()
    {
        $this->dummyValidator->validate(10000);
    }
}
