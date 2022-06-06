<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function refactor(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $type, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);
        $class = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $type, $property->flags);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
}
