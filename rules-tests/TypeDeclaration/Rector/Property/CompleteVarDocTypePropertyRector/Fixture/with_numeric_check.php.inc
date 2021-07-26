<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class WithNumericCheck
{
    /**
     * @var int|string
     */
    public $value;

    public function __construct($value = '')
    {
        if (is_numeric($value)) {
            $this->value = $value;
            return;
        }

        $this->value = 100;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class WithNumericCheck
{
    /**
     * @var float|int|string
     */
    public $value;

    public function __construct($value = '')
    {
        if (is_numeric($value)) {
            $this->value = $value;
            return;
        }

        $this->value = 100;
    }
}

?>
