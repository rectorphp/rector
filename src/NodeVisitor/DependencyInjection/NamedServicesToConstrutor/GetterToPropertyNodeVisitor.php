<?php declare(strict_types=1);

namespace Rector\NodeVisitor\DependencyInjection\NamedServicesToConstrutor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\Builder\Naming\NameResolver;
use Rector\Tests\NodeVisitor\DependencyInjection\NamedServicesToConstructorReconstructor\Source\LocalKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Converts all:
 * $this->get('some_service')
 *
 * into:
 * $this->someService
 */
final class GetterToPropertyNodeVisitor extends NodeVisitorAbstract
{
    /**
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
     * @return null|int|Node
     */
    public function enterNode(Node $node)
    {
        if ($this->isCandidate($node)) {
            $this->reconstruct($node);
        }

        return null;
    }

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    private function isCandidate(Node $node): bool
    {
        // $var = $this->get('some_service');
        // $var = $this->get('some_service')->getData();
        if ($node instanceof Assign) {
            if ($node->expr instanceof MethodCall || $node->var instanceof MethodCall) {
                if ($this->isContainerGetCall($node->expr)) {
                    return true;
                }
            }
        }

        // ['var => $this->get('some_service')->getData()]
        if ($node instanceof MethodCall && $node->var instanceof MethodCall) {
            if ($this->isContainerGetCall($node->var)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Assign|MethodCall $assignOrMethodCallNode
     */
    public function reconstruct(Node $assignOrMethodCallNode): void
    {
        if ($assignOrMethodCallNode instanceof Assign) {
            $this->processAssignment($assignOrMethodCallNode);
        }
    }

    private function processAssignment(Assign $assignNode): void
    {
        $refactoredMethodCall = $this->processMethodCallNode($assignNode->expr);
        if ($refactoredMethodCall) {
            $assignNode->expr = $refactoredMethodCall;
        }
    }

    /**
     * @todo extract to helper service, LocalKernelProvider::get...()
     */
    private function getContainerFromKernelClass(): ContainerInterface
    {
        /** @var Kernel $kernel */
        $kernel = new LocalKernel('dev', true);
        $kernel->boot();

        // @todo: initialize without creating cache or log directory
        // @todo: call only loadBundles() and initializeContainer() methods

        return $kernel->getContainer();
    }

    /**
     * Is "$this->get('string')" statements?
     */
    private function isContainerGetCall(MethodCall $methodCall): bool
    {
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

        $container = $this->getContainerFromKernelClass();
        if (! $container->has($serviceName)) {
            // service name could not be found
            return null;
        }

        $service = $container->get($serviceName);

        return get_class($service);
    }

    private function processMethodCallNode(MethodCall $methodCall): ?PropertyFetch
    {
        // Get service type
        $serviceType = $this->resolveServiceTypeFromMethodCall($methodCall);
        if ($serviceType === null) {
            return null;
        }

        // Get property name
        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        // creates "$this->propertyName"
        return new PropertyFetch(
            new Variable('this', [
                'name' => $propertyName
            ]), $propertyName
        );

    }
}
