<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Use_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\Php80\NodeFactory\AttrGroupsFactory;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
/**
 * @api used in Rector packages
 */
final class GenericAnnotationToAttributeConverter
{
    /**
     * @readonly
     */
    private AttrGroupsFactory $attrGroupsFactory;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    public function __construct(AttrGroupsFactory $attrGroupsFactory, ReflectionProvider $reflectionProvider, UseImportsResolver $useImportsResolver, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover)
    {
        $this->attrGroupsFactory = $attrGroupsFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function convert(Node $node, AnnotationToAttribute $annotationToAttribute) : ?AttributeGroup
    {
        if (!$this->isExistingAttributeClass($annotationToAttribute)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveBareUses();
        return $this->processDoctrineAnnotationClass($phpDocInfo, $uses, $annotationToAttribute);
    }
    /**
     * @param Use_[] $uses
     */
    private function processDoctrineAnnotationClass(PhpDocInfo $phpDocInfo, array $uses, AnnotationToAttribute $annotationToAttribute) : ?AttributeGroup
    {
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return null;
        }
        $doctrineTagAndAnnotationToAttributes = [];
        $doctrineTagValueNodes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            if (!$doctrineTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }
            $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute($doctrineTagValueNode, $annotationToAttribute);
            $doctrineTagValueNodes[] = $doctrineTagValueNode;
        }
        $attributeGroups = $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes, $uses);
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
        }
        return $attributeGroups[0] ?? null;
    }
    private function isExistingAttributeClass(AnnotationToAttribute $annotationToAttribute) : bool
    {
        // make sure the attribute class really exists to avoid error on early upgrade
        if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
            return \false;
        }
        // make sure the class is marked as attribute
        $classReflection = $this->reflectionProvider->getClass($annotationToAttribute->getAttributeClass());
        return $classReflection->isAttributeClass();
    }
}
