<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

final class SomeClass
{
    public function action()
    {
        return new SomeResponse();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

final class SomeClass
{
    public function action(): \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse
    {
        return new SomeResponse();
    }
}

?>
