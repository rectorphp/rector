<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanNot\SimplifyDeMorganBinaryRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr;

class MultiBinary
{
    public function run(Node $node)
    {
        return !($node instanceof Node\Name || $node instanceof Expr\StaticCall || $node instanceof Expr\Array_);

        return !($node instanceof Node\Name || $node instanceof Expr\StaticCall);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\BooleanNot\SimplifyDeMorganBinaryRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr;

class MultiBinary
{
    public function run(Node $node)
    {
        return !($node instanceof Node\Name || $node instanceof Expr\StaticCall || $node instanceof Expr\Array_);

        return !$node instanceof Node\Name && !$node instanceof Expr\StaticCall;
    }
}

?>
