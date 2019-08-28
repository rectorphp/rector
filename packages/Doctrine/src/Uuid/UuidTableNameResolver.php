<?php declare(strict_types=1);

namespace Rector\Doctrine\Uuid;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class UuidTableNameResolver
{
    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    public function __construct(DoctrineDocBlockResolver $doctrineDocBlockResolver)
    {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }

    /**
     * Creates unique many-to-many table name like: first_table_uuid_second_table_uuid
     */
    public function resolveManyToManyTableNameForProperty(Property $property): string
    {
        /** @var string $currentClass */
        $currentClass = $property->getAttribute(AttributeKey::CLASS_NAME);
        $shortCurrentClass = $this->resolveShortClassName($currentClass);

        $targetEntity = $this->doctrineDocBlockResolver->getTargetEntity($property);
        if ($targetEntity === null) {
            throw new ShouldNotHappenException(__METHOD__);
        }

        $shortTargetEntity = $this->resolveShortClassName($targetEntity);

        return strtolower($shortCurrentClass . '_uuid_' . $shortTargetEntity . '_uuid');
    }

    private function resolveShortClassName(string $currentClass): string
    {
        if (! Strings::contains($currentClass, '\\')) {
            return $currentClass;
        }

        return (string) Strings::after($currentClass, '\\', -1);
    }
}
