<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\Annotation\AnnotationItemsResolver;
use Rector\BetterPhpDocParser\AnnotationReader\NodeAnnotationReader;
use Rector\BetterPhpDocParser\PhpDocParser\AnnotationContentResolver;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\PHPStan\Type\ObjectTypeSpecifier;

abstract class AbstractPhpDocNodeFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/548EJJ/1
     */
    private const CLASS_CONST_REGEX = '#::class#';

    /**
     * @var NodeAnnotationReader
     */
    protected $nodeAnnotationReader;

    /**
     * @var AnnotationContentResolver
     */
    protected $annotationContentResolver;

    /**
     * @var AnnotationItemsResolver
     */
    protected $annotationItemsResolver;

    /**
     * @var CurrentNodeProvider
     */
    protected $currentNodeProvider;

    /**
     * @var ObjectTypeSpecifier
     */
    private $objectTypeSpecifier;

    /**
     * @required
     */
    public function autowireAbstractPhpDocNodeFactory(
        NodeAnnotationReader $nodeAnnotationReader,
        AnnotationContentResolver $annotationContentResolver,
        AnnotationItemsResolver $annotationItemsResolver,
        ObjectTypeSpecifier $objectTypeSpecifier,
        CurrentNodeProvider $currentNodeProvider
    ): void {
        $this->nodeAnnotationReader = $nodeAnnotationReader;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->annotationItemsResolver = $annotationItemsResolver;
        $this->objectTypeSpecifier = $objectTypeSpecifier;
        $this->currentNodeProvider = $currentNodeProvider;
    }

    protected function resolveContentFromTokenIterator(TokenIterator $tokenIterator): string
    {
        return $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);
    }

    protected function resolveFqnTargetEntity(string $targetEntity, Node $node): string
    {
        $targetEntity = $this->getCleanedUpTargetEntity($targetEntity);
        if (class_exists($targetEntity)) {
            return $targetEntity;
        }

        $namespacedTargetEntity = $node->getAttribute(AttributeKey::NAMESPACE_NAME) . '\\' . $targetEntity;
        if (class_exists($namespacedTargetEntity)) {
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
