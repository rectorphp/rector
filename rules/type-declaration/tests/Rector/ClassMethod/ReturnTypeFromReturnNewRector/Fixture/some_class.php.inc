<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

final class SomeClass
{
    public function action(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse
    {
        return new SomeResponse();
    }
}

?>
