<?php declare(strict_types=1);

namespace Rector\NodeValueResolver;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr;

final class NodeValueResolver
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    public function __construct(ConstExprEvaluator $constExprEvaluator)
    {
        $this->constExprEvaluator = $constExprEvaluator;
    }

    /**
     * @return string|bool|null
     */
    public function resolve(Node $node)
    {
        if ($node instanceof Expr) {
            return $this->constExprEvaluator->evaluateDirectly($node);
        }

        return null;
    }
}
