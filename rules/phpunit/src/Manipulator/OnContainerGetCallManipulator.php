<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\Collector\FormerVariablesByMethodCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\SymfonyPHPUnit\Naming\ServiceNaming;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeAnalyzer;

final class OnContainerGetCallManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ServiceNaming
     */
    private $serviceNaming;

    /**
     * @var KernelTestCaseNodeAnalyzer
     */
    private $kernelTestCaseNodeAnalyzer;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var FormerVariablesByMethodCollector
     */
    private $formerVariablesByMethodCollector;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        KernelTestCaseNodeAnalyzer $kernelTestCaseNodeAnalyzer,
        NodeNameResolver $nodeNameResolver,
        NodesToRemoveCollector $nodesToRemoveCollector,
        ServiceNaming $serviceNaming,
        ValueResolver $valueResolver,
        FormerVariablesByMethodCollector $formerVariablesByMethodCollector
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->serviceNaming = $serviceNaming;
        $this->kernelTestCaseNodeAnalyzer = $kernelTestCaseNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->formerVariablesByMethodCollector = $formerVariablesByMethodCollector;
    }

    /**
     * E.g. $someService ↓
     * $this->someService
     */
    public function replaceFormerVariablesWithPropertyFetch(Class_ $class): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node): ?PropertyFetch {
            if (! $node instanceof Variable) {
                return null;
            }

            $variableName = $this->nodeNameResolver->getName($node);
            if ($variableName === null) {
                return null;
            }

            /** @var string|null $methodName */
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName === null) {
                return null;
            }

            $serviceType = $this->formerVariablesByMethodCollector->getTypeByVariableByMethod(
                $methodName,
                $variableName
            );
            if ($serviceType === null) {
                return null;
            }

            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($serviceType);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }

    public function removeAndCollectFormerAssignedVariables(Class_ $class, bool $skipSetUpMethod = true): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $skipSetUpMethod
        ): ?PropertyFetch {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if ($skipSetUpMethod && $this->kernelTestCaseNodeAnalyzer->isSetUpOrEmptyMethod($node)) {
                return null;
            }

            if (! $this->kernelTestCaseNodeAnalyzer->isOnContainerGetMethodCall($node)) {
                return null;
            }

            $type = $this->valueResolver->getValue($node->args[0]->value);
            if ($type === null) {
                return null;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                $this->processAssign($node, $parentNode, $type);
                return null;
            }

            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($type);
            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }

    private function processAssign(MethodCall $methodCall, Assign $assign, string $type): void
    {
        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return;
        }

        /** @var string $methodName */
        $methodName = $methodCall->getAttribute(AttributeKey::METHOD_NAME);

        $this->formerVariablesByMethodCollector->addMethodVariable($methodName, $variableName, $type);
        $this->nodesToRemoveCollector->addNodeToRemove($assign);
    }
}
