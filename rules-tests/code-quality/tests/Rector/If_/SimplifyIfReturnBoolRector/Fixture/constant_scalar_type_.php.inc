<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\NullType;

class ConstantScalarType_
{
    public function run(ConstantScalarType $type)
    {
        if ($type instanceof NullType) {
            return null;
        }

        if (! $type->getValue()) {
            return false;
        }

        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\NullType;

class ConstantScalarType_
{
    public function run(ConstantScalarType $type)
    {
        if ($type instanceof NullType) {
            return null;
        }
        return (bool) $type->getValue();
    }
}

?>
