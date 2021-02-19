<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\Contract\FirstInterface;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\Contract\SecondInterface;

final class DoNotImplementTwice implements FirstInterface, SecondInterface
{
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\Contract\FirstInterface;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\Contract\SecondInterface;

final class DoNotImplementTwice implements \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\Contract\ThirdInterface
{
}

?>
