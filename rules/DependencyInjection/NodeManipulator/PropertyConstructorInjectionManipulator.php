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
use Rector\PostRector\DependencyInjection\PropertyAdder;

final class PropertyConstructorInjectionManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocTypeChanger $phpDocTypeChanger,
        PhpDocTagRemover $phpDocTagRemover,
        PropertyAdder $propertyAdder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->propertyAdder = $propertyAdder;
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

        $this->propertyAdder->addConstructorDependencyToClass($classLike, $type, $propertyName, $property->flags);
    }
}
