<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\NodeFactory\EntityUuidNodeFactory;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

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
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(EntityUuidNodeFactory $entityUuidNodeFactory, ClassManipulator $classManipulator)
    {
        $this->entityUuidNodeFactory = $entityUuidNodeFactory;
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add uuid initializion to all entities that misses it');
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

        $this->classManipulator->addStmtsToClassMethodIfNotThereYet($node, '__construct', $stmts);

        return $node;
    }

    private function resolveUuidPropertyFromClass(Class_ $class): ?Property
    {
        foreach ($class->getProperties() as $property) {
            $propertyPhpDoc = $this->getPhpDocInfo($property);
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
        $constructClassMethod = $class->getMethod('__construct');
        if ($constructClassMethod === null) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirst((array) $class->stmts, function (Node $node) use (
            $uuidPropertyName
        ): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $node->expr instanceof StaticCall) {
                return false;
            }

            $staticCall = $node->expr;
            if (! $this->isObjectType($staticCall->class, 'Ramsey\Uuid\Uuid')) {
                return false;
            }

            if (! $this->isName($staticCall->name, 'uuid4')) {
                return false;
            }
            return $this->isName($node->var, $uuidPropertyName);
        });
    }
}
