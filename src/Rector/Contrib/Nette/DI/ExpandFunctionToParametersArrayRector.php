<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/298/files
 *
 * From:
 * $builder->expand('argument');
 * $builder->expand('%argument%');
 *
 * To:
 * $builder->parameters['argument'];
 * $builder->parameters['argument'];
 */
final class ExpandFunctionToParametersArrayRector extends AbstractRector
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

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Nette\DI\CompilerExtension') {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node, 'expand')) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        return $this->methodArgumentAnalyzer->isMethodFirstArgumentString($methodCallNode);
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns $builder->parameters['argument']
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        /** @var String_ $argument */
        $argument = $methodCallNode->args[0]->value;
        $argument->value = Strings::trim($argument->value, '%');

        return $this->nodeFactory->createVariablePropertyArrayFetch($methodCallNode->var, 'parameters', $argument);
    }
}
