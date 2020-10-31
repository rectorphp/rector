<?php

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Source;

final class SomeClass
{
}

final class SomeClass_Exception
{
}

?>
-----
<?php

namespace Rector\PSR4\Tests\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector\Source;

final class SomeClass
{
}

?>
