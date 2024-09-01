<?php

declare (strict_types=1);
namespace Rector\Doctrine\PhpDoc;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier;
final class ShortClassExpander
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;
    /**
     * @var string
     * @see https://regex101.com/r/548EJJ/1
     */
    private const CLASS_CONST_REGEX = '#::class#';
    public function __construct(ReflectionProvider $reflectionProvider, ObjectTypeSpecifier $objectTypeSpecifier)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
    }
    /**
     * @api
     */
    public function resolveFqnTargetEntity(string $targetEntity, Node $node) : string
    {
        $targetEntity = $this->getCleanedUpTargetEntity($targetEntity);
        if ($this->reflectionProvider->hasClass($targetEntity)) {
            return $targetEntity;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return $targetEntity;
        }
        $namespacedTargetEntity = $scope->getNamespace() . '\\' . $targetEntity;
        if ($this->reflectionProvider->hasClass($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }
        $resolvedType = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, new ObjectType($targetEntity), $scope);
        if ($resolvedType instanceof ShortenedObjectType) {
            return $resolvedType->getFullyQualifiedName();
        }
        // probably tested class
        return $targetEntity;
    }
    private function getCleanedUpTargetEntity(string $targetEntity) : string
    {
        return Strings::replace($targetEntity, self::CLASS_CONST_REGEX, '');
    }
}
