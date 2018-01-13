<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * This Rector handles 2 things:
 * - removes "$return this;" method bodies
 * - changes fluent calls to standalone calls
 */
final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $relatedTypesAndMethods;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof Return_) {
            $returnExpr = $node->expr;

            if (! $returnExpr instanceof Variable) {
                return false;
            }

            return $returnExpr->name === 'this';
        }

        if ($node instanceof MethodCall) {
            foreach ($this->relatedTypesAndMethods as $type => $methods) {
                if (! $this->methodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param Return_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Return_) {
            $this->removeNode = true;

            $className = $node->getAttribute(Attribute::CLASS_NAME);
            $methodName = $node->getAttribute(Attribute::METHOD_NAME);

            $this->relatedTypesAndMethods[$className][] = $methodName;

            return null;
        }

        if ($node instanceof MethodCall) {
        }

        return $node;
    }
}
