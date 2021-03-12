<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ReturnString;

class IfConditionalUnionNullable
{
    /** @var string|null */
    private $nullOrString;

    public function __construct(?ReturnString $returnString = null)
    {
        if ($returnString !== null) {
            $this->nullOrString = $returnString->getName();
        }
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ReturnString;

class IfConditionalUnionNullable
{
    private ?string $nullOrString = null;

    public function __construct(?ReturnString $returnString = null)
    {
        if ($returnString !== null) {
            $this->nullOrString = $returnString->getName();
        }
    }
}

?>
