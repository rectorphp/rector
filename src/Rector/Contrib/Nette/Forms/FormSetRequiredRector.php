<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeChanger\IdentifierRenamer;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;

/**
 * Covers https://forum.nette.org/cs/26672-missing-setrequired-true-false-on-field-abc-in-form
 */
final class FormSetRequiredRector extends AbstractRector
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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypesAndMethods(
            $node,
            ['Nette\Forms\Controls\TextInput'],
            ['addCondition']
        )
        ) {
            return false;
        }

        /** @var MethodCall $node */
        if (count($node->args) !== 1) {
            return false;
        }

        $arg = $node->args[0];
        if (! $arg->value instanceof ClassConstFetch) {
            return false;
        }

        $classConstFetchNode = $arg->value;

        $argTypes = $this->nodeTypeResolver->resolve($classConstFetchNode->class);
        if (! in_array('Nette\Application\UI\Form', $argTypes, true)) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $classConstFetchNode->name;

        return $identifierNode->toString() === 'FILLED';
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->identifierRenamer->renameNode($methodCallNode, 'setRequired');
        $methodCallNode->args = $this->nodeFactory->createArgs([
            false,
        ]);

        return $methodCallNode;
    }
}
