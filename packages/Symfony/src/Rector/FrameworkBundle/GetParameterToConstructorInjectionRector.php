<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Naming\PropertyNaming;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class GetParameterToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    /**
     * @var string
     */
    private $controllerClass;

    public function __construct(
        PropertyNaming $propertyNaming,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        string $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller'
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->controllerClass = $controllerClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        $this->getParameter('someParameter');
    }
}
CODE_SAMPLE
                    ,
<<<'CODE_SAMPLE'
class MyCommand extends Command
{
    private $someParameter;

    public function __construct($someParameter)
    {
        $this->someParameter = $someParameter;
    }

    public function someMethod()
    {
        $this->someParameter;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->controllerClass)) {
            return null;
        }

        if (! $this->isName($node, 'getParameter')) {
            return null;
        }

        /** @var String_ $stringArgument */
        $stringArgument = $node->args[0]->value;
        $parameterName = $stringArgument->value;
        $propertyName = $this->propertyNaming->underscoreToName($parameterName);

        $this->addPropertyToClass(
            (string) $node->getAttribute(Attribute::CLASS_NAME),
            'string', // @todo: resolve type from container provider? see parameter autowire compiler pass
            $propertyName
        );

        return $this->propertyFetchNodeFactory->createLocalWithPropertyName($propertyName);
    }
}
