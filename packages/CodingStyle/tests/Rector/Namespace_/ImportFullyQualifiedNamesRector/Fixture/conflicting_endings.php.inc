<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node;

final class AddDefaultValueForUndefinedVariableRector
{
    /**
     * @return Node\Stmt\Unset_|Node\Expr\Cast\Unset_
     */
    public function refactor()
    {
        if ($parentNode instanceof Node\Stmt\Unset_ || $parentNode instanceof Node\Expr\Cast\Unset_) {
            return null;
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node;

final class AddDefaultValueForUndefinedVariableRector
{
    /**
     * @return Unset_|Node\Expr\Cast\Unset_
     */
    public function refactor()
    {
        if ($parentNode instanceof Unset_ || $parentNode instanceof Node\Expr\Cast\Unset_) {
            return null;
        }
    }
}

?>
