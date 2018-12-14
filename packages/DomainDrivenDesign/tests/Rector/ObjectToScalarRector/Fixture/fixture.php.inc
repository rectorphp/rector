<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector\Source\SomeChildOfValueObject;

class ActionClass
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

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector\Source\SomeChildOfValueObject;

class ActionClass
{
    /**
     * @var SomeChildOfValueObject|null
     */
    private $someChildValueObject;

    public function someFunction()
    {
        $this->someChildValueObject = 'value';

        $someChildValueObject = new SomeChildOfValueObject();
    }
}

?>
