<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDoc;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

final class ShortClassExpander
{
    /**
     * @var string
     * @see https://regex101.com/r/548EJJ/1
     */
    private const CLASS_CONST_REGEX = '#::class#';

    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private ObjectTypeSpecifier $objectTypeSpecifier
    ) {
    }

    public function resolveFqnTargetEntity(string $targetEntity, Node $node): string
    {
        $targetEntity = $this->getCleanedUpTargetEntity($targetEntity);
        if ($this->reflectionProvider->hasClass($targetEntity)) {
            return $targetEntity;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return $targetEntity;
        }

        $namespacedTargetEntity = $scope->getNamespace() . '\\' . $targetEntity;
        if ($this->reflectionProvider->hasClass($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }

        $resolvedType = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType(
            $node,
            new ObjectType($targetEntity)
        );
        if ($resolvedType instanceof ShortenedObjectType) {
            return $resolvedType->getFullyQualifiedName();
        }

        // probably tested class
        return $targetEntity;
    }

    private function getCleanedUpTargetEntity(string $targetEntity): string
    {
        return Strings::replace($targetEntity, self::CLASS_CONST_REGEX, '');
    }
}
