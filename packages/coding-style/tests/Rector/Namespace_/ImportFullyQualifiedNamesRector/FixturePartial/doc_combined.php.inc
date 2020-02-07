<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Arg;

final class CallableThisArrayToAnonymousFunctionRector
{
    /**
     * @param Node\Param[] $params
     * @return Node\Arg[]
     */
    private function convertParamsToArgs(array $params): array
    {
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use PhpParser\Node\Param;
use PhpParser\Node;
use PhpParser\Node\Arg;

final class CallableThisArrayToAnonymousFunctionRector
{
    /**
     * @param Param[] $params
     * @return Arg[]
     */
    private function convertParamsToArgs(array $params): array
    {
    }
}

?>
