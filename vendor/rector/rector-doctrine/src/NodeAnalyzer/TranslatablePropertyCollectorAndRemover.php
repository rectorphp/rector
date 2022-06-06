<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use RectorPrefix20220606\Rector\Doctrine\ValueObject\PropertyNameAndPhpDocInfo;
use RectorPrefix20220606\Rector\Doctrine\ValueObject\PropertyNamesAndPhpDocInfos;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeRemoval\NodeRemover;
final class TranslatablePropertyCollectorAndRemover
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpDocInfoFactory $phpDocInfoFactory, NodeRemover $nodeRemover, NodeNameResolver $nodeNameResolver)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeRemover = $nodeRemover;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function processClass(Class_ $class) : PropertyNamesAndPhpDocInfos
    {
        $propertyNameAndPhpDocInfos = [];
        foreach ($class->getProperties() as $property) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByAnnotationClass('Gedmo\\Mapping\\Annotation\\Locale')) {
                $this->nodeRemover->removeNode($property);
                continue;
            }
            $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Gedmo\\Mapping\\Annotation\\Translatable');
            if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);
            $propertyName = $this->nodeNameResolver->getName($property);
            $propertyNameAndPhpDocInfos[] = new PropertyNameAndPhpDocInfo($propertyName, $phpDocInfo);
            $this->nodeRemover->removeNode($property);
        }
        return new PropertyNamesAndPhpDocInfos($propertyNameAndPhpDocInfos);
    }
}
