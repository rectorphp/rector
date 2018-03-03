<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * - $this->getParameter('someParameter') # where "some_service" is name of parameter
 *
 * into:
 * - public function __construct($someParameter)
 * - {
 * -      $this->someParameter = $someParameter;
 * - }
 *
 * - ...
 *
 * - $this->someService
 */
final class GetParameterToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $getParameterAwareTypes = [
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
        'Symfony\Component\DependencyInjection\ContainerAwareInterface',
    ];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(
        PropertyNaming $propertyNaming,
        ClassPropertyCollector $classPropertyCollector,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        MethodCallAnalyzer $methodCallAnalyzer
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->methodCallAnalyzer->isTypesAndMethods($node, $this->getParameterAwareTypes, ['getParameter']);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        /** @var String_ $stringArgument */
        $stringArgument = $methodCallNode->args[0]->value;
        $parameterName = $stringArgument->value;
        $propertyName = $this->propertyNaming->underscoreToName($parameterName);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            ['string'], // @todo: resolve type from container provider? see parameter autowire compiler pass
            $propertyName
        );

        return $this->propertyFetchNodeFactory->createLocalWithPropertyName($propertyName);
    }
}
