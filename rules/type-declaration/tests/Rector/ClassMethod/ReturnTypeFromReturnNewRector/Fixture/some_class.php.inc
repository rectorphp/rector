<?php

<<<<<<< HEAD
<<<<<<< HEAD
declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

=======
namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
final class SomeClass
{
    public function action()
    {
<<<<<<< HEAD
<<<<<<< HEAD
        return new SomeResponse();
=======
        return new Response();
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
        return new SomeResponse();
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    }
}

?>
-----
<?php

<<<<<<< HEAD
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
=======
declare(strict_types=1);

>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse;

final class SomeClass
{
    public function action(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Source\SomeResponse
    {
<<<<<<< HEAD
        return new Response();
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
        return new SomeResponse();
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    }
}

?>
