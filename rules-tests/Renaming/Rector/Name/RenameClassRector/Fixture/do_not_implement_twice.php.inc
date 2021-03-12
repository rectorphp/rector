<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\Contract\FirstInterface;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\Contract\SecondInterface;

final class DoNotImplementTwice implements FirstInterface, SecondInterface
{
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\Contract\FirstInterface;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\Contract\SecondInterface;

final class DoNotImplementTwice implements \Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\Contract\ThirdInterface
{
}

?>
