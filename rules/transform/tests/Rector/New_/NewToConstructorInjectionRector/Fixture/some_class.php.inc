<?php

namespace Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

class SomeClass
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

namespace Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

class SomeClass
{
    private \Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator $dummyValidator;
    public function __construct(\Rector\Transform\Tests\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator $dummyValidator)
    {
        $this->dummyValidator = $dummyValidator;
    }
    public function run()
    {
        $this->dummyValidator->validate(100000);
    }
}

?>
