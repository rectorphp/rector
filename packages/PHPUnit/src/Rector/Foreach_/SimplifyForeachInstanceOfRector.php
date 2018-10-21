<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\NodeAnalyzer\ForeachAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyForeachInstanceOfRector extends AbstractRector
{
    /**
     * @var ForeachAnalyzer
     */
    private $foreachAnalyzer;

    public function __construct(ForeachAnalyzer $foreachAnalyzer)
    {
        $this->foreachAnalyzer = $foreachAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify unnecessary foreach check of instances', [
            new CodeSample(
                <<<'CODE_SAMPLE'
foreach ($foos as $foo) {
    $this->assertInstanceOf(\SplFileInfo::class, $foo);
}
CODE_SAMPLE
                ,
                '$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);'
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $matchedNode = $this->foreachAnalyzer->matchOnlyStmt(
            $node,
            function (Node $node, Foreach_ $foreachNode): ?Node {
                if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
                    return null;
                }

                if (! $this->isName($node, 'assertInstanceOf')) {
                    return null;
                }

                if (! $this->areNodesEqual($foreachNode->valueVar, $node->args[1]->value)) {
                    return null;
                }

                return $node;
            }
        );

        if (! $matchedNode) {
            return null;
        }

        /** @var MethodCall|StaticCall $matchedNode */
        $callClass = get_class($matchedNode);

        $callNode = new $callClass(
            $this->resolveVar($matchedNode),
            new Name('assertContainsOnlyInstancesOf'),
            [$matchedNode->args[0], new Arg($node->expr)]
        );

        return new Expression($callNode);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function resolveVar(Node $node): Node
    {
        if ($node instanceof MethodCall) {
            return $node->var;
        }

        return $node->class;
    }
}
