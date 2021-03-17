<?php

namespace Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\FixturePhp80;

use Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

final class SomeClassToPromotedProperty
{
    public function run()
    {
        $dummyValidator = new DummyValidator();
        $dummyValidator->validate(100000);
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\FixturePhp80;

use Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

final class SomeClassToPromotedProperty
{
    public function __construct(private \Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator $dummyValidator)
    {
    }
    public function run()
    {
        $this->dummyValidator->validate(100000);
    }
}

?>
