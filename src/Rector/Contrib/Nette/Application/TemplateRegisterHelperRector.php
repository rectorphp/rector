<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Application;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $this->template->registerHelper('someFilter', ...);
 *
 * After:
 * - $this->template->getLatte()->addFilter('someFilter', ...)
 */
final class TemplateRegisterHelperRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Nette\Bridges\ApplicationLatte\Template',
            'registerHelper'
        );
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): Node
    {
        $methodCallNode->name = new Identifier('addFilter');

        $propertyFetchNode = $this->nodeFactory->clonePropertyFetch($methodCallNode->var);

        $methodCallNode->var = $this->nodeFactory->createMethodCallWithVariable($propertyFetchNode, 'getLatte');

        return $methodCallNode;
    }
}
