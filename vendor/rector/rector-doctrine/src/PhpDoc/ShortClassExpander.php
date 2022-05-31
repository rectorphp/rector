<?php

declare (strict_types=1);
namespace Rector\Doctrine\PhpDoc;

use RectorPrefix20220531\Nette\Utils\Strings;
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
     * @var string
     * @see https://regex101.com/r/548EJJ/1
     */
    private const CLASS_CONST_REGEX = '#::class#';
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
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\TypeDeclaration\PHPStan\ObjectTypeSpecifier $objectTypeSpecifier)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
    }
    /**
     * @api
     */
    public function resolveFqnTargetEntity(string $targetEntity, \PhpParser\Node $node) : string
    {
        $targetEntity = $this->getCleanedUpTargetEntity($targetEntity);
        if ($this->reflectionProvider->hasClass($targetEntity)) {
            return $targetEntity;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return $targetEntity;
        }
        $namespacedTargetEntity = $scope->getNamespace() . '\\' . $targetEntity;
        if ($this->reflectionProvider->hasClass($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }
        $resolvedType = $this->objectTypeSpecifier->narrowToFullyQualifiedOrAliasedObjectType($node, new \PHPStan\Type\ObjectType($targetEntity), $scope);
        if ($resolvedType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return $resolvedType->getFullyQualifiedName();
        }
        // probably tested class
        return $targetEntity;
    }
    private function getCleanedUpTargetEntity(string $targetEntity) : string
    {
        return \RectorPrefix20220531\Nette\Utils\Strings::replace($targetEntity, self::CLASS_CONST_REGEX, '');
    }
}
