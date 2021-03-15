<?php

namespace Rector\Tests\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Fixture;

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

namespace Rector\Tests\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Fixture;


?>
