<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt;

final class DoNotChangeCorrectUnionArrayDocblockOnReturn
{
    /**
     * @return int|string[]
     */
    public function run(): int|array
    {
    }

    /**
     * @return Node|null|Stmt[]
     */
    public function run2(): Node|null|array
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt;

final class DoNotChangeCorrectUnionArrayDocblockOnReturn
{
    /**
     * @return int|string[]
     */
    public function run()
    {
    }

    /**
     * @return Node|null|Stmt[]
     */
    public function run2()
    {
    }
}

?>
