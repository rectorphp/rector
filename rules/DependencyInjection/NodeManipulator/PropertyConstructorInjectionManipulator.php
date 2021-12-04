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
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;

final class PropertyConstructorInjectionManipulator
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly PhpDocTagRemover $phpDocTagRemover,
        private readonly PropertyToAddCollector $propertyToAddCollector,
        private readonly BetterNodeFinder $betterNodeFinder,
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

        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        if (! $class instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $propertyMetadata = new PropertyMetadata($propertyName, $type, $property->flags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
}
