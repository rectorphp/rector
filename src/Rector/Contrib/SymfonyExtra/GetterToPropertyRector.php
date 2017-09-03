<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\SymfonyExtra;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Kernel\ServiceFromKernelResolver;
use Rector\Builder\Naming\NameResolver;
use Rector\Deprecation\SetNames;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Tests\Rector\Contrib\SymfonyExtra\GetterToPropertyRector\Source\LocalKernel;

/**
 * Converts all:
 * $this->get('some_service') # where "some_service" is name of the service in container.
 *
 * into:
 * $this->someService # where "someService" is type of the service
 */
final class GetterToPropertyRector extends AbstractRector
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ServiceFromKernelResolver
     */
    private $serviceFromKernelResolver;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        NameResolver $nameResolver,
        ServiceFromKernelResolver $serviceFromKernelResolver,
        ClassPropertyCollector $classPropertyCollector,
        NodeFactory $nodeFactory
    ) {
        $this->nameResolver = $nameResolver;
        $this->serviceFromKernelResolver = $serviceFromKernelResolver;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        // finds $var = $this->get('some_service');
        // finds $var = $this->get('some_service')->getData();
        if ($node instanceof Assign && ($node->expr instanceof MethodCall || $node->var instanceof MethodCall)) {
            if ($this->isContainerGetCall($node->expr)) {
                return true;
            }
        }

        // finds ['var => $this->get('some_service')->getData()]
        if ($node instanceof MethodCall && $node->var instanceof MethodCall) {
            if ($this->isContainerGetCall($node->var)) {
                return true;
            }
        }

        return false;
    }

    public function refactor(Node $assignOrMethodCallNode): ?Node
    {
        if ($assignOrMethodCallNode instanceof Assign) {
            $refactoredMethodCall = $this->processMethodCallNode($assignOrMethodCallNode->expr);
            if ($refactoredMethodCall) {
                $assignOrMethodCallNode->expr = $refactoredMethodCall;
            }
        }

        if ($assignOrMethodCallNode instanceof MethodCall) {
            $refactoredMethodCall = $this->processMethodCallNode($assignOrMethodCallNode->var);
            if ($refactoredMethodCall) {
                $assignOrMethodCallNode->var = $refactoredMethodCall;
            }
        }

        return $assignOrMethodCallNode;
    }

    public function getSetName(): string
    {
        return SetNames::SYMFONY_EXTRA;
    }

    public function sinceVersion(): float
    {
        return 3.3;
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

    private function processMethodCallNode(MethodCall $methodCall): ?PropertyFetch
    {
        $serviceType = $this->serviceFromKernelResolver->resolveServiceClassFromArgument($methodCall->args[0], LocalKernel::class);
        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass($this->getClassName(), $serviceType, $propertyName);

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }
}
