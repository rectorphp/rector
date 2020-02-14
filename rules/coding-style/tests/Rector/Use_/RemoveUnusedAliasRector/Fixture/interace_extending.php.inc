<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractInterface as NotNeededAliasInterface;

interface BasicInterface extends NotNeededAliasInterface
{
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractInterface;

interface BasicInterface extends AbstractInterface
{
}

?>
