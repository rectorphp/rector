<?php declare(strict_types=1);

namespace Rector\NodeVisitor\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\Kernel\ServiceFromKernelResolver;
use Rector\Builder\Naming\NameResolver;
use Rector\Builder\PropertyBuilder;
use Rector\Tests\NodeVisitor\DependencyInjection\NamedServicesToConstructorReconstructor\Source\LocalKernel;

final class NamedServicesToConstructorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ServiceFromKernelResolver
     */
    private $serviceFromKernelResolver;

    public function __construct(
        ConstructorMethodBuilder $constructorMethodBuilder,
        PropertyBuilder $propertyBuilder,
        NameResolver $nameResolver,
        ServiceFromKernelResolver $serviceFromKernelResolver
    ) {
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->propertyBuilder = $propertyBuilder;
        $this->nameResolver = $nameResolver;
        $this->serviceFromKernelResolver = $serviceFromKernelResolver;
    }

    private function isCandidate(Node $node): bool
    {
        return $node instanceof Class_;
    }

    /**
     * @param Class_ $classNode
     */
    public function reconstruct(Node $classNode): void
    {
        foreach ($classNode->stmts as $insideClassNode) {
            // 1. Detect method
            if (! $insideClassNode instanceof ClassMethod) {
                continue;
            }

            $methodNode = $insideClassNode;
            foreach ($methodNode->stmts as $insideMethodNode) {
                $insideMethodNode = $insideMethodNode->expr;

                if ($insideMethodNode instanceof MethodCall && $insideMethodNode->var instanceof MethodCall) {
                    $this->processOnServiceMethodCall($classNode, $insideMethodNode);

                // B. Find $var = $this->get('...');
                } elseif ($insideMethodNode instanceof Assign) {
                    $this->processAssignment($classNode, $insideMethodNode);
                }
            }
        }
    }

    private function processOnServiceMethodCall(Class_ $classNode, MethodCall $methodCallNode): void
    {
        if (! $this->isContainerGetCall($methodCallNode)) {
            return;
        }

        $refactoredMethodCall = $this->processMethodCallNode($classNode, $methodCallNode->var);
        if ($refactoredMethodCall) {
            $methodCallNode->var = $refactoredMethodCall;
        }
    }

    private function processAssignment(Class_ $classNode, Assign $assignNode): void
    {
        if (!$this->isContainerGetCall($assignNode)) {
            return;
        }

        $this->processMethodCallNode($classNode, $assignNode->expr);

        /*$refactoredMethodCall = */
//        if ($refactoredMethodCall) {
//            $assignNode->expr = $refactoredMethodCall;
//        }
    }

    /**
     * Accept only "$this->get('string')" statements.
     */
    private function isContainerGetCall(Node $node): bool
    {
        if ($node instanceof Assign && ($node->expr instanceof MethodCall || $node->var instanceof MethodCall)) {
            $methodCall = $node->expr;
        } elseif ($node instanceof MethodCall && $node->var instanceof MethodCall) {
            $methodCall = $node->var;
        } else {
            return false;
        }

        if ($methodCall->var->name !== 'this') {
            return false;
        }

        if ((string) $methodCall->name !== 'get') {
            return false;
        }

        if (! $methodCall->args[0]->value instanceof String_) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall|Expr $methodCallNode
     */
    private function resolveServiceTypeFromMethodCall($methodCallNode): ?string
    {
        /** @var String_ $argument */
        $argument = $methodCallNode->args[0]->value;
        $serviceName = $argument->value;

        return $this->serviceFromKernelResolver->resolveServiceClassByNameFromKernel(
            $serviceName, LocalKernel::class
        );
    }

    private function processMethodCallNode(Class_ $classNode, MethodCall $methodCall): ?PropertyFetch
    {
        // Get service type
        $serviceType = $this->resolveServiceTypeFromMethodCall($methodCall);
        if ($serviceType === null) {
            return null;
        }

        // Get property name
        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        // Add property assignment to constructor
        $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $serviceType, $propertyName);

        // 5. Add property to class
        $this->propertyBuilder->addPropertyToClass($classNode, $serviceType, $propertyName);

        // creates "$this->propertyName"
        return new PropertyFetch(
            new Variable('this', [
                'name' => $propertyName
            ]), $propertyName
        );

    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::DONT_TRAVERSE_CHILDREN
     *        => Children of $node are not traversed. $node stays as-is
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|int|Node Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            $this->reconstruct($node);
        }

        return null;
    }
}
