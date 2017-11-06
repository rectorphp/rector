<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Console;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\Contrib\Symfony\ContainerCallAnalyzer;
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
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    /**
     * @var ContainerCallAnalyzer
     */
    private $containerCallAnalyzer;

    /**
     * @var ServiceTypeForNameProviderInterface
     */
    private $serviceTypeForNameProvider;

    public function __construct(
        ClassPropertyCollector $classPropertyCollector,
        PropertyNaming $propertyNaming,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        ContainerCallAnalyzer $containerCallAnalyzer,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider
    ) {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->propertyNaming = $propertyNaming;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->containerCallAnalyzer = $containerCallAnalyzer;
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

        return $this->containerCallAnalyzer->isGetContainerCall($node);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $this->replaceParentContainerAwareCommandWithCommand($methodCallNode);

        /** @var String_ $serviceNameArgument */
        $serviceNameArgument = $methodCallNode->args[0]->value;

        $serviceName = $serviceNameArgument->value;
        $serviceType = $this->serviceTypeForNameProvider->provideTypeForName($serviceName);
        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->propertyNaming->typeToName($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            [$serviceType],
            $propertyName
        );

        return $this->propertyFetchNodeFactory->createLocalWithPropertyName($propertyName);
    }

    private function replaceParentContainerAwareCommandWithCommand(Node $node): void
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        $classNode->extends = new FullyQualified(self::COMMAND_CLASS);
    }
}
