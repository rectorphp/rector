<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 *
 * Before:
 * class MyCommand extends ContainerAwareCommand
 * {
 *      // ...
 *      $this->getContainer()->get('some_service');
 *      $this->container->get('some_service');
 * }
 *
 * After:
 * class MyCommand extends Command
 * {
 *      public function __construct(SomeService $someService)
 *      {
 *          $this->someService = $someService;
 *      }
 *
 *      // ...
 *      $this->someService
 * }
 */
final class CommandToConstructorInjectionRector extends AbstractRector
{
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
     * @var ServiceTypeForNameProviderInterface
     */
    private $serviceTypeForNameProvider;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var string[]
     */
    private $containerAwareParentTypes = [
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
    ];

    public function __construct(
        ClassPropertyCollector $classPropertyCollector,
        PropertyNaming $propertyNaming,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider,
        MethodCallAnalyzer $methodCallAnalyzer
    ) {
        $this->classPropertyCollector = $classPropertyCollector;
        $this->propertyNaming = $propertyNaming;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->serviceTypeForNameProvider = $serviceTypeForNameProvider;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'get'
        )) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        return in_array($parentClassName, $this->containerAwareParentTypes, true);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
//        $this->replaceParentContainerAwareCommandWithCommand($methodCallNode);

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

//    private function replaceParentContainerAwareCommandWithCommand(Node $node): void
//    {
//        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
//        $classNode->extends = new FullyQualified(self::COMMAND_CLASS);
//    }
}
