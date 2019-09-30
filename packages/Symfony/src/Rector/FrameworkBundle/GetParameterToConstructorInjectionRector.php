<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\StringType;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @see \Rector\Symfony\Tests\Rector\FrameworkBundle\GetParameterToConstructorInjectionRector\GetParameterToConstructorInjectionRectorTest
 */
final class GetParameterToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of parameters via `getParameter()` in ContainerAware to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
<<<'PHP'
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        $this->getParameter('someParameter');
    }
}
PHP
                    ,
<<<'PHP'
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
PHP
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
        if (! $this->isObjectType($node->var, Controller::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'getParameter')) {
            return null;
        }

        /** @var String_ $stringArgument */
        $stringArgument = $node->args[0]->value;
        $parameterName = $stringArgument->value;
        $propertyName = $this->propertyNaming->underscoreToName($parameterName);

        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        $this->addPropertyToClass(
            $classNode,
            new StringType(), // @todo: resolve type from container provider? see parameter autowire compiler pass
            $propertyName
        );

        return $this->createPropertyFetch('this', $propertyName);
    }
}
