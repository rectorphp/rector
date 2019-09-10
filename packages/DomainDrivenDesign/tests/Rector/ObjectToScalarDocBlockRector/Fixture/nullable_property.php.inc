<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeChildOfValueObject;

class NullableProperty
{
    /**
     * @var SomeChildOfValueObject|null
     */
    private $someChildValueObject;
}

?>
-----
<?php

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Fixture;

use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\Source\SomeChildOfValueObject;

class NullableProperty
{
    /**
     * @var string|null
     */
    private $someChildValueObject;
}

?>
