<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\SymfonyExtra;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Kernel\ServiceFromKernelResolver;
use Rector\Builder\Naming\NameResolver;
use Rector\Deprecation\SetNames;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\SymfonyContainerCallsAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Tests\Rector\Contrib\SymfonyExtra\GetterToPropertyRector\Source\LocalKernel;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
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
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var SymfonyContainerCallsAnalyzer
     */
    private $symfonyContainerCallsAnalyzer;

    public function __construct(
        ServiceFromKernelResolver $serviceFromKernelResolver,
        ClassPropertyCollector $classPropertyCollector,
        NameResolver $nameResolver,
        NodeFactory $nodeFactory,
        SymfonyContainerCallsAnalyzer $symfonyContainerCallsAnalyzer
    ) {
        $this->serviceFromKernelResolver = $serviceFromKernelResolver;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nameResolver = $nameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->symfonyContainerCallsAnalyzer = $symfonyContainerCallsAnalyzer;
    }

    public function getSetName(): string
    {
        return SetNames::SYMFONY_EXTRA;
    }

    public function sinceVersion(): float
    {
        return 3.3;
    }

    public function isCandidate(Node $node): bool
    {
        $class = (string) $node->getAttribute(Attribute::CLASS_NAME);

        if (! Strings::endsWith($class, 'Command')) {
            return false;
        }

        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->symfonyContainerCallsAnalyzer->isGetContainerCall($node);
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->replaceParentContainerAwareCommandWithCommand($node);

        $serviceType = $this->serviceFromKernelResolver->resolveServiceClassFromArgument(
            $node->args[0],
            LocalKernel::class
        );

        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $node->getAttribute(Attribute::CLASS_NAME),
            $serviceType,
            $propertyName
        );

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }

    private function replaceParentContainerAwareCommandWithCommand(Node $node): void
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $classNode->extends = new FullyQualified('Symfony\Component\Console\Command\Command');
    }
}
