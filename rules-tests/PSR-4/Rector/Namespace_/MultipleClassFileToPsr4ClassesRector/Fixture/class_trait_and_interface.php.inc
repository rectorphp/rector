<?php

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Fixture;

final class ClassTraitAndInterface
{
}

interface MyInterface
{
}


trait MyTrait
{
}

?>
-----
<?php

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Fixture;


?>
