<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;

final class PropertyConstructorInjectionManipulator
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private PhpDocTagRemover $phpDocTagRemover,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function refactor(
        Property $property,
        Type $type,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): void {
        $propertyName = $this->nodeNameResolver->getName($property);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $propertyMetadata = new PropertyMetadata($propertyName, $type, $property->flags);
        $this->propertyToAddCollector->addPropertyToClass($classLike, $propertyMetadata);
    }
}
