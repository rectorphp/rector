<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\Manipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\Doctrine\NodeFactory\EntityUuidNodeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\AlwaysInitializeUuidInEntityRectorTest
 */
final class AlwaysInitializeUuidInEntityRector extends AbstractRector
{
    /**
     * @var EntityUuidNodeFactory
     */
    private $entityUuidNodeFactory;

    /**
     * @var ClassDependencyManipulator
     */
    private $classDependencyManipulator;

    public function __construct(
        ClassDependencyManipulator $classDependencyManipulator,
        EntityUuidNodeFactory $entityUuidNodeFactory
    ) {
        $this->entityUuidNodeFactory = $entityUuidNodeFactory;
        $this->classDependencyManipulator = $classDependencyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add uuid initializion to all entities that misses it', [
            new CodeSample(
<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AddUuidInit
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AddUuidInit
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;
    public function __construct()
    {
        $this->superUuid = \Ramsey\Uuid\Uuid::uuid4();
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isDoctrineEntityClass($node)) {
            return null;
        }

        $uuidProperty = $this->resolveUuidPropertyFromClass($node);
        if ($uuidProperty === null) {
            return null;
        }

        $uuidPropertyName = $this->getName($uuidProperty);
        if ($this->hasUuidInitAlreadyAdded($node, $uuidPropertyName)) {
            return null;
        }

        $stmts = [];
        $stmts[] = $this->entityUuidNodeFactory->createUuidPropertyDefaultValueAssign($uuidPropertyName);

        $this->classDependencyManipulator->addStmtsToConstructorIfNotThereYet($node, $stmts);

        return $node;
    }

    private function resolveUuidPropertyFromClass(Class_ $class): ?Property
    {
        foreach ($class->getProperties() as $property) {
            $propertyPhpDoc = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($propertyPhpDoc === null) {
                continue;
            }

            $varType = $propertyPhpDoc->getVarType();
            if (! $varType instanceof ObjectType) {
                continue;
            }

            if (! Strings::contains($varType->getClassName(), 'UuidInterface')) {
                continue;
            }

            return $property;
        }

        return null;
    }

    private function hasUuidInitAlreadyAdded(Class_ $class, string $uuidPropertyName): bool
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirst((array) $class->stmts, function (Node $node) use (
            $uuidPropertyName
        ): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $this->isStaticCallNamed($node->expr, 'Ramsey\Uuid\Uuid', 'uuid4')) {
                return false;
            }

            return $this->isName($node->var, $uuidPropertyName);
        });
    }
}
