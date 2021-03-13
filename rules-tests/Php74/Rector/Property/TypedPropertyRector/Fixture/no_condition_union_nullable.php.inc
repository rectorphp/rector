<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ReturnString;

class NoConditionUnionNullable
{
    /** @var string|null */
    private $nullOrString;

    public function __construct(?ReturnString $returnString = null)
    {
        $this->nullOrString = $returnString->getNameOrNull();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ReturnString;

class NoConditionUnionNullable
{
    private ?string $nullOrString = null;

    public function __construct(?ReturnString $returnString = null)
    {
        $this->nullOrString = $returnString->getNameOrNull();
    }
}

?>
