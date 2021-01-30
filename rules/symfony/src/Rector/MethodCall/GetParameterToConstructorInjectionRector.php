<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\GetParameterToConstructorInjectionRector\GetParameterToConstructorInjectionRectorTest
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
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
        if (! $this->isObjectType($node->var, 'Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
            return null;
        }

        if (! $this->isName($node->name, 'getParameter')) {
            return null;
        }

        /** @var String_ $stringArgument */
        $stringArgument = $node->args[0]->value;
        $parameterName = $stringArgument->value;

        $parameterName = Strings::replace($parameterName, '#\.#', '_');

        $propertyName = $this->propertyNaming->underscoreToName($parameterName);

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $this->addConstructorDependencyToClass($classLike, new StringType(), $propertyName);

        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
}
