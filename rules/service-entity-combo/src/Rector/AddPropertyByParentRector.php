<?php

declare(strict_types=1);

namespace App\Rector;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AddPropertyByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    private ClassInsertManipulator $classInsertManipulator;

    /**
     * @var string[]
     */
    private array $parentsDependenciesToAdd;

    public function __construct(
        ClassInsertManipulator $classInsertManipulator
    ) {
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        $constructorClassMethod = $node->getMethod('__construct');
        if ($constructorClassMethod === null) {
            return null;
        }

        if ($node->extends === null) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        foreach ($this->parentsDependenciesToAdd as $parentClass => $paramToAdd) {
            if ($parentClassName !== $parentClass) {
                continue;
            }

            $this->classInsertManipulator->addPropertyToClass(
                $node,
                $this->fullyQualifiedNameToParamNameConverter($paramToAdd),
                new ObjectType($paramToAdd)
            );

            $constructorClassMethod->params[] = $this->createParam($paramToAdd);
        }

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'ServiceEntityToConstructorInjection'
        );
    }

    private function createParam(string $fullyQualifiedName): Param
    {
        return new Param(new Variable($this->fullyQualifiedNameToParamNameConverter($fullyQualifiedName)), null, new FullyQualified(
            $fullyQualifiedName
        ));
    }

    private function fullyQualifiedNameToParamNameConverter(string $fullyQualifiedName): string
    {
        return lcfirst(str_replace('Interface','', (new \ReflectionClass($fullyQualifiedName))->getShortName()));
    }

    public function configure(array $configuration): void
    {
        $this->parentsDependenciesToAdd = $configuration['$parentsDependenciesToAdd'] ?? [];
    }
}