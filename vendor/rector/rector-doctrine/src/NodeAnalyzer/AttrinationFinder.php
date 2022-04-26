<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
final class AttrinationFinder
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->attributeFinder = $attributeFinder;
    }
    /**
     * @param class-string $name
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param $node
     * @return \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute|null
     */
    public function getByOne($node, string $name)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo && $phpDocInfo->hasByAnnotationClass($name)) {
            return $phpDocInfo->getByAnnotationClass($name);
        }
        return $this->attributeFinder->findAttributeByClass($node, $name);
    }
    /**
     * @param class-string $name
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Param $node
     */
    public function hasByOne($node, string $name) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo && $phpDocInfo->hasByAnnotationClass($name)) {
            return \true;
        }
        $attribute = $this->attributeFinder->findAttributeByClass($node, $name);
        return $attribute instanceof \PhpParser\Node\Attribute;
    }
    /**
     * @param class-string[] $names
     */
    public function hasByMany(\PhpParser\Node\Stmt\Property $property, array $names) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo && $phpDocInfo->hasByAnnotationClasses($names)) {
            return \true;
        }
        $attribute = $this->attributeFinder->findAttributeByClasses($property, $names);
        return $attribute instanceof \PhpParser\Node\Attribute;
    }
}
