<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveCodeAfterReturnRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove dead code after return statement', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $a)
    {
         return $a;
         $a++;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $a)
    {
         return $a;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FunctionLike::class];
    }

    /**
     * @param Closure|ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        $isDeadAfterReturn = false;

        foreach ($node->stmts as $key => $stmt) {
            if ($isDeadAfterReturn) {
                unset($node->stmts[$key]);
            }

            if ($stmt instanceof Return_) {
                $isDeadAfterReturn = true;
                continue;
            }
        }

        return $node;
    }
}
