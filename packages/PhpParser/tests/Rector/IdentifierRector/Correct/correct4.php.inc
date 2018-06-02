<?php declare(strict_types=1);

namespace Roave\BetterReflection\NodeCompiler;

use PhpParser\Node;

class SomeClass
{
    /**
     * @var Node\Stmt\ClassMethod|Node\Stmt\Function_|Node\Expr\Closure
     */
    private $node;

    /**
     * Get the "short" name of the function (e.g. for A\B\foo, this will return
     * "foo").
     *
     * @return string
     */
    public function getShortName() : string
    {
        if ($this->node instanceof Node\Expr\Closure) {
            return self::CLOSURE_NAME;
        }

        return $this->node->name->toString();
    }
}
