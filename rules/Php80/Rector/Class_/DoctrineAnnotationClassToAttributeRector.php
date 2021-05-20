<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see https://github.com/doctrine/annotations/blob/1.13.x/lib/Doctrine/Common/Annotations/Annotation/Target.php
 * @see https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/docs/en/custom.rst#annotation-required
 *
 * @see \Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\DoctrineAnnotationClassToAttributeRectorTest
 */
final class DoctrineAnnotationClassToAttributeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @see https://github.com/doctrine/annotations/blob/e6e7b7d5b45a2f2abc5460cc6396480b2b1d321f/lib/Doctrine/Common/Annotations/Annotation/Target.php#L24-L29
     * @var array<string, string>
     */
    private const TARGET_TO_CONSTANT_MAP = [
        'METHOD' => 'TARGET_METHOD',
        'PROPERTY' => 'TARGET_PROPERTY',
        'CLASS' => 'TARGET_CLASS',
        'FUNCTION' => 'TARGET_FUNCTION',
        'ALL' => 'TARGET_ALL',
        // special case
        'ANNOTATION' => 'TARGET_CLASS',
    ];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor Doctrine @annotation annotated class to a PHP 8.0 attribute class', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class SomeAnnotation
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SomeAnnotation
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        if (!$phpDocInfo->hasByNames(['Annotation', 'annotation'])) {
            return null;
        }
        $this->phpDocTagRemover->removeByName($phpDocInfo, 'annotation');
        $this->phpDocTagRemover->removeByName($phpDocInfo, 'Annotation');
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass('Attribute');
        $this->decorateTarget($phpDocInfo, $attributeGroup);
        foreach ($node->getProperties() as $property) {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            if (!$propertyPhpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
                continue;
            }
            $requiredDoctrineAnnotationTagValueNode = $propertyPhpDocInfo->getByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\Required');
            if (!$requiredDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($propertyPhpDocInfo, $requiredDoctrineAnnotationTagValueNode);
            // require in constructor
            $propertyName = $this->getName($property);
            $this->addConstructorDependencyToClass($node, new \PHPStan\Type\MixedType(), $propertyName, \PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC);
            $this->removeNode($property);
        }
        $node->attrGroups[] = $attributeGroup;
        return $node;
    }
    /**
     * @param array<string, mixed> $targetValues
     * @return ClassConstFetch[]
     */
    private function resolveFlags(array $targetValues) : array
    {
        $flags = [];
        foreach (self::TARGET_TO_CONSTANT_MAP as $target => $constant) {
            if (!\in_array($target, $targetValues, \true)) {
                continue;
            }
            $flags[] = $this->nodeFactory->createClassConstFetch('Attribute', $constant);
        }
        return $flags;
    }
    /**
     * @param ClassConstFetch[] $flags
     * @return ClassConstFetch|BitwiseOr|null
     */
    private function createFlagCollection(array $flags) : ?\PhpParser\Node\Expr
    {
        if ($flags === []) {
            return null;
        }
        $flagCollection = \array_shift($flags);
        foreach ($flags as $flag) {
            $flagCollection = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($flagCollection, $flag);
        }
        return $flagCollection;
    }
    private function decorateTarget(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\AttributeGroup $attributeGroup) : void
    {
        $targetDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\Target');
        if (!$targetDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return;
        }
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $targetDoctrineAnnotationTagValueNode);
        $targets = $targetDoctrineAnnotationTagValueNode->getSilentValue();
        if (!$targets instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return;
        }
        $targetValues = $targets->getValuesWithExplicitSilentAndWithoutQuotes();
        $flags = $this->resolveFlags($targetValues);
        $flagCollection = $this->createFlagCollection($flags);
        if ($flagCollection === null) {
            return;
        }
        $attributeGroup->attrs[0]->args[] = new \PhpParser\Node\Arg($flagCollection);
    }
}
