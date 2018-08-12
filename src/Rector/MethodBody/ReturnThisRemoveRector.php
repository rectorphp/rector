<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Inspiration:
 * - https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * - http://www.yegor256.com/2018/03/13/fluent-interfaces.html
 * - https://github.com/guzzle/guzzle/commit/668209c895049759377593eed129e0949d9565b7#diff-810cdcfdd8a6b9e1fc0d1e96d7786874
 */
final class ReturnThisRemoveRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes "return $this;" form fluent interfaces.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction()
    {
    }

    public function otherFunction()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return Return_::class;
    }

    /**
     * @param Return_ $returnNode
     */
    public function refactor(Node $returnNode): ?Node
    {
        if (! $returnNode->expr instanceof Variable) {
            return $returnNode;
        }

        if ($returnNode->expr->name !== 'this') {
            return $returnNode;
        }

        $this->removeNode = true;

        return null;
    }
}
