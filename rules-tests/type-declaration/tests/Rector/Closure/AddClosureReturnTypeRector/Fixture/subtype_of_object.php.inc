<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

class SubtypeOfObject
{
    public function run($stmt)
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable([$stmt], function (Node $node)
        {
            if (!$node instanceof String_) {
                return $node;
            }

            $match = Strings::match($node->value, '#(\\$|\\\\)(?<number>\d+)#');
            if (!$match) {
                return $node;
            }

            $matchesVariable = new Variable('matches');

            return new ArrayDimFetch($matchesVariable, new LNumber((int)$match['number']));
        });
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

class SubtypeOfObject
{
    public function run($stmt)
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable([$stmt], function (Node $node): \PhpParser\Node
        {
            if (!$node instanceof String_) {
                return $node;
            }

            $match = Strings::match($node->value, '#(\\$|\\\\)(?<number>\d+)#');
            if (!$match) {
                return $node;
            }

            $matchesVariable = new Variable('matches');

            return new ArrayDimFetch($matchesVariable, new LNumber((int)$match['number']));
        });
    }
}

?>
