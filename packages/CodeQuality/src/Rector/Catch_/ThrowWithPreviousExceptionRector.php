<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/thecodingmachine/phpstan-strict-rules/blob/e3d746a61d38993ca2bc2e2fcda7012150de120c/src/Rules/Exceptions/ThrowMustBundlePreviousExceptionRule.php#L83
 * @see \Rector\CodeQuality\Tests\Rector\Catch_\ThrowWithPreviousExceptionRector\ThrowWithPreviousExceptionRectorTest
 */
final class ThrowWithPreviousExceptionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'When throwing into a catch block, checks that the previous exception is passed to the new throw clause',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups');
        }
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        try {
            $someCode = 1;
        } catch (Throwable $throwable) {
            throw new AnotherException('ups', $throwable->getCode(), $throwable);
        }
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $catchedThrowableVariable = $node->var;

        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use ($catchedThrowableVariable): ?int {
            if (! $node instanceof Throw_) {
                return null;
            }

            if (! $node->expr instanceof New_) {
                return null;
            }

            if (! $node->expr->class instanceof Name) {
                return null;
            }

            // exception is bundled
            if (isset($node->expr->args[2])) {
                return null;
            }

            if (! isset($node->expr->args[1])) {
                // get previous code
                $node->expr->args[1] = new Arg(new MethodCall($catchedThrowableVariable, 'getCode'));
            }

            $node->expr->args[2] = new Arg($catchedThrowableVariable);

            // nothing more to add
            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $node;
    }
}
