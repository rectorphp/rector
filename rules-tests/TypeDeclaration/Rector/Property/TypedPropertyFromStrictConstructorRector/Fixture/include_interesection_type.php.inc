<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Source\FirstType;
use Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Source\SecondType;

final class IncludeIntersectionType
{
    private $firstAndSecond;

    public function __construct(FirstType&SecondType $firstAndSecond)
    {
        $this->firstAndSecond = $firstAndSecond;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Source\FirstType;
use Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Source\SecondType;

final class IncludeIntersectionType
{
    private \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Source\FirstType&\Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Source\SecondType $firstAndSecond;

    public function __construct(FirstType&SecondType $firstAndSecond)
    {
        $this->firstAndSecond = $firstAndSecond;
    }
}

?>
