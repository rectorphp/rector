<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AssertIssetToSpecificMethodRector extends AbstractPHPUnitRector
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

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        IdentifierRenamer $identifierRenamer,
        NodeFactory $nodeFactory
    ) {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
        $this->nodeFactory = $nodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns isset comparisons to their method name alternatives in PHPUnit TestCase', [
            new CodeSample(
                '$this->assertTrue(isset($anything->foo));',
                '$this->assertFalse(isset($anything["foo"]), "message");'
            ),
            new CodeSample(
                '$this->assertObjectHasAttribute("foo", $anything);',
                '$this->assertArrayNotHasKey("foo", $anything, "message");'
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $methodCallNode instanceof MethodCall) {
            return null;
        }
        if (! $this->isInTestClass($methodCallNode)) {
            return null;
        }
        if (! $this->methodCallAnalyzer->isMethods($methodCallNode, ['assertTrue', 'assertFalse'])) {
            return null;
        }
        /** @var MethodCall $methodCallNode */
        $methodCallNode = $methodCallNode;
        $firstArgumentValue = $methodCallNode->args[0]->value;
        // is property access
        if (! $firstArgumentValue instanceof Isset_) {
            return null;
        }
        $variableNodeClass = get_class($firstArgumentValue->vars[0]);
        if (in_array($variableNodeClass, [ArrayDimFetch::class, PropertyFetch::class], true) === false) {
            return null;
        }
        /** @var Isset_ $issetNode */
        $issetNode = $methodCallNode->args[0]->value;

        $issetNodeArg = $issetNode->vars[0];

        if ($issetNodeArg instanceof PropertyFetch) {
            $this->refactorPropertyFetchNode($methodCallNode, $issetNodeArg);
        } elseif ($issetNodeArg instanceof ArrayDimFetch) {
            $this->refactorArrayDimFetchNode($methodCallNode, $issetNodeArg);
        }

        return $methodCallNode;
    }

    private function refactorPropertyFetchNode(MethodCall $node, PropertyFetch $propertyFetchNode): void
    {
        $this->identifierRenamer->renameNodeWithMap($node, [
            'assertTrue' => 'assertObjectHasAttribute',
            'assertFalse' => 'assertObjectNotHasAttribute',
        ]);

        $oldArgs = $node->args;

        unset($oldArgs[0]);

        $node->args = array_merge($this->nodeFactory->createArgs([
            $this->nodeFactory->createString((string) $propertyFetchNode->name),
            $propertyFetchNode->var,
        ]), $oldArgs);
    }

    private function refactorArrayDimFetchNode(MethodCall $node, ArrayDimFetch $arrayDimFetchNode): void
    {
        $this->identifierRenamer->renameNodeWithMap($node, [
            'assertTrue' => 'assertArrayHasKey',
            'assertFalse' => 'assertArrayNotHasKey',
        ]);

        $oldArgs = $node->args;

        unset($oldArgs[0]);

        $node->args = array_merge($this->nodeFactory->createArgs([
            $arrayDimFetchNode->dim,
            $arrayDimFetchNode->var,
        ]), $oldArgs);
    }
}
