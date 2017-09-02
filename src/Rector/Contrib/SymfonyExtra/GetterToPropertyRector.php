<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\SymfonyExtra;

use PhpParser\Node;
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
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->isContainerGetCall($node);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return $this->processMethodCallNode($methodCallNode);
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
        if ($methodCall->var->name !== 'this' || (string) $methodCall->name !== 'get') {
            return false;
        }

        if (count($methodCall->args) !== 1 || ! $methodCall->args[0]->value instanceof String_) {
            return false;
        }

        return true;
    }

    private function processMethodCallNode(MethodCall $methodCall): ?PropertyFetch
    {
        $serviceType = $this->serviceFromKernelResolver->resolveServiceClassFromArgument(
            $methodCall->args[0],
            LocalKernel::class
        );

        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass($this->getClassName(), $serviceType, $propertyName);

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }
}
