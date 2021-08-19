<?php

namespace Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

class MultiAssign
{
    public function run()
    {
        $dummyValidator = $temp =  new DummyValidator();
        $dummyValidator->validate(100000);
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;

class MultiAssign
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
