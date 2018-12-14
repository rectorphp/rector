<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeChildOfValueObject;

class FirstActionClass
{
    /**
     * @var SomeChildOfValueObject|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = new SomeChildOfValueObject('value');

        $someChildValueObject = new SomeChildOfValueObject();
    }
}

?>
-----
<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeChildOfValueObject;

class FirstActionClass
{
    /**
     * @var string|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = new SomeChildOfValueObject('value');

        $someChildValueObject = new SomeChildOfValueObject();
    }
}

?>
