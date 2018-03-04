<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\Class_\VariableInfo;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class RemoveParentDoctrineRepositoryRector extends AbstractRector
{
    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;
    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    public function __construct(PropertyBuilder $propertyBuilder, ConstructorMethodBuilder $constructorMethodBuilder)
    {
        $this->propertyBuilder = $propertyBuilder;
        $this->constructorMethodBuilder = $constructorMethodBuilder;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        if (! $node->extends) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Doctrine\ORM\EntityRepository') {
            return false;
        }

        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith($className, 'Repository');
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // remove parent class
        $node->extends = null;

        // add $repository property
        $parameterInfo = VariableInfo::createFromNameAndTypes('repository', ['Doctrine\ORM\EntityRepository']);
        $this->propertyBuilder->addPropertyToClass($node, $parameterInfo);

        // add repository to constuctor
        $methodCall = new MethodCall(new Variable('entityManager'), 'getRepository');
        $propertyInfo = VariableInfo::createFromNameAndTypes('entityManager', ['Doctrine\ORM\EntityManager']);
        $this->constructorMethodBuilder->addPropertyWithExpression($node, $propertyInfo, $methodCall, $parameterInfo);

        return $node;
    }
}
