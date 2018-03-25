<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/298/files
 * From:
 * - $builder->expand(object|array);
 *
 * To:
 * - \Nette\DI\Helpers::expand(object|array, $builder->parameters);
 */
final class ExpandFunctionToStaticExpandFunctionRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        MethodArgumentAnalyzer $methodArgumentAnalyzer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param MethodCall $node
     */
    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Nette\DI\CompilerExtension') {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node, 'expand')
            || $this->methodArgumentAnalyzer->isMethodFirstArgumentString($node)) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns \Nette\DI\Helpers::expand('argument', $builder->parameters)
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $arguments = [
            $methodCallNode->args[0],
            new PropertyFetch($methodCallNode->var, 'parameters'),
        ];

        return $this->nodeFactory->createStaticMethodCallWithArgs('Nette\DI\Helpers', 'expand', $arguments);
    }
}
