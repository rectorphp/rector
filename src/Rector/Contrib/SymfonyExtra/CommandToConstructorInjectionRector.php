<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\SymfonyExtra;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Kernel\ServiceFromKernelResolver;
use Rector\Builder\Naming\NameResolver;
use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractRector;
use Rector\Tests\Rector\Contrib\SymfonyExtra\GetterToPropertyRector\Source\LocalKernel;
use Symfony\Component\Console\Command\Command;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 *
 * Similar to @see \Rector\Rector\Contrib\Symfony\GetterToPropertyRector
 * @todo Extract common logic!
 *
 * Before:
 * class MyCommand extends ContainerAwareCommand
 *
 * $this->getContainer()->get('some_service');
 *
 * After:
 * class MyCommand extends Command
 *
 * public function construct(SomeService $someService)
 * {
 *     $this->someService = $someService;
 * }
 *
 * ...
 *
 * $this->someService
 */
final class CommandToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var ServiceFromKernelResolver
     */
    private $serviceFromKernelResolver;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var Class_
     */
    private $classNode;

    public function __construct(
        ServiceFromKernelResolver $serviceFromKernelResolver,
        ClassPropertyCollector $classPropertyCollector,
        NameResolver $nameResolver
    ) {
        $this->serviceFromKernelResolver = $serviceFromKernelResolver;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nameResolver = $nameResolver;
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
     * @todo add node traverser for this or to AbstractRector
     * @param Node[] $nodes
     * @return null|Node[]
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classNode = null;

        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->classNode = $node;
                break;
            }
        }

        return null;
    }


    public function isCandidate(Node $node): bool
    {
        if (! Strings::endsWith($this->getClassName(), 'Command')) {
            return false;
        }

        // $this->getContainer()->get('some_service');
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof MethodCall) {
            return false;
        }

        if ((string) $node->var->var->name !== 'this') {
            return false;
        }

        if ((string) $node->name !== 'get') {
            return false;
        }

        if (! isset($node->args[0])) {
            return false;
        }

        if (! $node->args[0]->value instanceof String_) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->replaceParentContainerAwareCommandWithCommand();

        $serviceName = $node->args[0]->value->value;

        $serviceType = $this->serviceFromKernelResolver->resolveServiceClassByNameFromKernel(
            $serviceName,
            LocalKernel::class
        );

        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass($this->getClassName(), $serviceType, $propertyName);

        return $this->createPropertyFetch($propertyName);
    }

    /**
     * @todo move to NodeFactory...
     * Creates "$this->propertyName".
     */
    private function createPropertyFetch(string $propertyName): PropertyFetch
    {
        return new PropertyFetch(
            new Node\Expr\Variable('this', [
                'name' => $propertyName,
            ]),
            $propertyName
        );
    }

    /**
     * @todo move to parent class?
     */
    private function getClassName(): string
    {
        return $this->classNode->namespacedName->toString();
    }

    private function replaceParentContainerAwareCommandWithCommand(): void
    {
        $this->classNode->extends = new Name('\Symfony\Component\Console\Command\Command');
    }
}
