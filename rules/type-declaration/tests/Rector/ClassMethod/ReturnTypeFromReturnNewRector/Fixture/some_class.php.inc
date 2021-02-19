<?php

<<<<<<< HEAD
declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

=======
namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
final class SomeClass
{
    public function action()
    {
<<<<<<< HEAD
        return new SomeResponse();
=======
        return new Response();
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
    }
}

?>
-----
<?php

<<<<<<< HEAD
declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

final class SomeClass
{
    public function action(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse
    {
        return new SomeResponse();
=======
namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class SomeClass
{
    public function action(): Respose
    {
        return new Response();
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
    }
}

?>
