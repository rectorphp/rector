<?php

namespace Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\FixturePhp80;

use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

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

namespace Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\FixturePhp80;

use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

final class SomeClassToPromotedProperty
{
    public function __construct(private \Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator $dummyValidator)
    {
    }
    public function run()
    {
        $this->dummyValidator->validate(100000);
    }
}

?>
