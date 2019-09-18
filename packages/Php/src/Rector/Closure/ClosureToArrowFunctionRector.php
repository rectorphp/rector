<?php declare(strict_types=1);

namespace Rector\Php\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/arrow_functions_v2
 * @see \Rector\Php\Tests\Rector\Closure\ClosureToArrowFunctionRector\ClosureToArrowFunctionRectorTest
 */
final class ClosureToArrowFunctionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change closure to arrow function', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            return is_object($meetup);
        });
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, fn(Meetup $meetup) => is_object($meetup));
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }

    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion('7.4')) {
            return null;
        }

        if (count((array) $node->stmts) !== 1) {
            return null;
        }

        if (! $node->stmts[0] instanceof Return_) {
            return null;
        }

        /** @var Return_ $return */
        $return = $node->stmts[0];
        if ($return->expr === null) {
            return null;
        }

        if ($this->shouldSkipForUsedReferencedValue($node, $return)) {
            return null;
        }

        $arrowFunction = new ArrowFunction();
        $arrowFunction->params = $node->params;
        $arrowFunction->returnType = $node->returnType;
        $arrowFunction->byRef = $node->byRef;

        $arrowFunction->expr = $return->expr;

        return $arrowFunction;
    }

    private function shouldSkipForUsedReferencedValue(Closure $closure, Return_ $return): bool
    {
        if ($return->expr === null) {
            return false;
        }

        $referencedValues = $this->resolveReferencedUseVariablesFromClosure($closure);
        if ($referencedValues === []) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirst([$return->expr], function (Node $node) use (
            $referencedValues
        ): bool {
            foreach ($referencedValues as $referencedValue) {
                if ($this->areNodesEqual($node, $referencedValue)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @return Variable[]
     */
    private function resolveReferencedUseVariablesFromClosure(Closure $closure): array
    {
        $referencedValues = [];

        /** @var ClosureUse $use */
        foreach ((array) $closure->uses as $use) {
            if ($use->byRef) {
                $referencedValues[] = $use->var;
            }
        }

        return $referencedValues;
    }
}
