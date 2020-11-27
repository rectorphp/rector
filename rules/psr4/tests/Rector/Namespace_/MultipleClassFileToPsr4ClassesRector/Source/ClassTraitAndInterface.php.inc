<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Expected;

interface MyInterface
{
}

final class MyClass
{
}

trait MyTrait
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Expected;


?>
