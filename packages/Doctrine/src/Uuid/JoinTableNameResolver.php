<?php declare(strict_types=1);

namespace Rector\Doctrine\Uuid;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class JoinTableNameResolver
{
    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(
        DoctrineDocBlockResolver $doctrineDocBlockResolver,
        ParsedNodesByType $parsedNodesByType
    ) {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->parsedNodesByType = $parsedNodesByType;
    }

    /**
     * Guessed many-to-many table name like: first_table_second_table
     */
    public function resolveManyToManyTableNameForProperty(Property $property): string
    {
        /** @var Class_ $currentClass */
        $currentClass = $property->getAttribute(AttributeKey::CLASS_NODE);
        $currentTableName = $this->resolveTableNameFromClass($currentClass);

        $targetEntity = $this->doctrineDocBlockResolver->getTargetEntity($property);
        if ($targetEntity === null) {
            throw new ShouldNotHappenException(__METHOD__);
        }

        $targetEntityClass = $this->parsedNodesByType->findClass($targetEntity);
        if ($targetEntityClass === null) {
            // dummy fallback
            $targetTableName = $this->resolveShortClassName($targetEntity);
        } else {
            $targetTableName = $this->resolveTableNameFromClass($targetEntityClass);
        }

        return strtolower($currentTableName . '_' . $targetTableName);
    }

    private function resolveShortClassName(string $currentClass): string
    {
        if (! Strings::contains($currentClass, '\\')) {
            return $currentClass;
        }

        return (string) Strings::after($currentClass, '\\', -1);
    }

    private function resolveTableNameFromClass(Class_ $class): string
    {
        $tableTagValueNode = $this->doctrineDocBlockResolver->getDoctrineTableTagValueNode($class);
        if ($tableTagValueNode !== null) {
            $tableName = $tableTagValueNode->getName();
            if ($tableName !== null) {
                return $tableName;
            }
        }

        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        return $this->resolveShortClassName($className);
    }
}
