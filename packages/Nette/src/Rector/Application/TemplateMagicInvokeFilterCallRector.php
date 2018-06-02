<?php declare(strict_types=1);

namespace Rector\Nette\Rector\Application;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class TemplateMagicInvokeFilterCallRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns properties with @inject to private properties and constructor injection', [
            new CodeSample(
                '$this->template->someFilter(...)',
                '$this->template->getLatte()->invokeFilter("someFilter", ...)'
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        // skip just added calls
        if ($node->getAttribute(Attribute::ORIGINAL_NODE) === null) {
            return false;
        }

        return $this->methodCallAnalyzer->isTypeAndMagic($node, 'Nette\Bridges\ApplicationLatte\Template');
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): Node
    {
        $this->changeToInvokeFilterMethodCall($methodCallNode);

        $methodCallNode->var = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $methodCallNode->var,
            'getLatte'
        );

        return $methodCallNode;
    }

    private function changeToInvokeFilterMethodCall(MethodCall $methodCallNode): void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;

        $filterName = $identifierNode->toString();
        $filterArguments = $methodCallNode->args;

        $this->identifierRenamer->renameNode($methodCallNode, 'invokeFilter');

        $methodCallNode->args[0] = $this->nodeFactory->createArg($filterName);
        $methodCallNode->args = array_merge($methodCallNode->args, $filterArguments);
    }
}
