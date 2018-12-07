<?php

namespace Rector\PhpParser\Tests\Rector\UseWithAliasRector\Fixture;

use PhpParser\Node\Stmt\UseUse;

function useWithAlias()
{
    array_map(function (UseUse $useUse) {
        return $useUse->alias;
    }, null);
}

?>
-----
<?php

namespace Rector\PhpParser\Tests\Rector\UseWithAliasRector\Fixture;

use PhpParser\Node\Stmt\UseUse;

function useWithAlias()
{
    array_map(function (UseUse $useUse) {
        return $useUse->getAlias()->toString();
    }, null);
}

?>
