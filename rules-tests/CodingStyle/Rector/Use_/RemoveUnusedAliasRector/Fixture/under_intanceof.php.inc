<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel as BaseKernel;

class UnderInstanceOf
{
    public function run(?BaseKernel $baseKernel)
    {
        if ($baseKernel instanceof BaseKernel) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel;

class UnderInstanceOf
{
    public function run(?AbstractKernel $baseKernel)
    {
        if ($baseKernel instanceof AbstractKernel) {
        }
    }
}

?>
