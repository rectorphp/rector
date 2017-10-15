<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Console;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Naming\NameResolver;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\SymfonyContainerCallsAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

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
     * @var string
     */
    private const COMMAND_CLASS = 'Symfony\Component\Console\Command\Command';

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

    /**
     * @var ServiceTypeForNameProviderInterface
     */
    private $serviceTypeForNameProvider;

    public function __construct(
        ClassPropertyCollector $classPropertyCollector,
        NameResolver $nameResolver,
        NodeFactory $nodeFactory,
        SymfonyContainerCallsAnalyzer $symfonyContainerCallsAnalyzer,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider
    ) {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nameResolver = $nameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->symfonyContainerCallsAnalyzer = $symfonyContainerCallsAnalyzer;
        $this->serviceTypeForNameProvider = $serviceTypeForNameProvider;
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
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->replaceParentContainerAwareCommandWithCommand($methodCallNode);

        $serviceName = $methodCallNode->args[0]->value->value;
        $serviceType = $this->serviceTypeForNameProvider->provideTypeForName($serviceName);
        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            $serviceType,
            $propertyName
        );

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }

    private function replaceParentContainerAwareCommandWithCommand(Node $node): void
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $classNode->extends = new FullyQualified(self::COMMAND_CLASS);
    }
}
