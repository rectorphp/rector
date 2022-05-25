<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\DifferentInterface;

interface WhenInterfaceAndParentInterface extends DifferentInterface
{
    public function renameMe(): int;
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Fixture;

use Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\Source\DifferentInterface;

interface WhenInterfaceAndParentInterface extends DifferentInterface
{
    public function toNewVersion(): int;
}

?>
