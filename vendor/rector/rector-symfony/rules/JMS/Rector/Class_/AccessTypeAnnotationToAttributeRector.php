<?php

declare (strict_types=1);
namespace Rector\Symfony\JMS\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\GenericAnnotationToAttributeConverter;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\JMSAnnotation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/schmittjoh/serializer/issues/1531
 *
 * @see \Rector\Symfony\Tests\JMS\Rector\Class_\AccessTypeAnnotationToAttributeRector\AccessTypeAnnotationToAttributeRectorTest
 */
final class AccessTypeAnnotationToAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private GenericAnnotationToAttributeConverter $genericAnnotationToAttributeConverter;
    public function __construct(DocBlockUpdater $docBlockUpdater, GenericAnnotationToAttributeConverter $genericAnnotationToAttributeConverter)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->genericAnnotationToAttributeConverter = $genericAnnotationToAttributeConverter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes @AccessType annotation to #[AccessType] attribute with specific key', [new CodeSample(<<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\AccessType;

/** @AccessType("public_method") */
class User
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\AccessType;

#[AccessType(values: ['public_method'])]
class User
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class, Property::class];
    }
    /**
     * @param Class_|Property $node
     * @return \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Property|null
     */
    public function refactor(Node $node)
    {
        $annotationToAttribute = new AnnotationToAttribute(JMSAnnotation::ACCESS_TYPE);
        $attributeGroup = $this->genericAnnotationToAttributeConverter->convert($node, $annotationToAttribute);
        if (!$attributeGroup instanceof AttributeGroup) {
            return null;
        }
        $attribute = $attributeGroup->attrs[0];
        if (\count($attribute->args) === 1) {
            $soleArg = $attribute->args[0];
            $soleArg->name = new Identifier('type');
        }
        // 2. Reprint docblock
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $node->attrGroups = \array_merge($node->attrGroups, [$attributeGroup]);
        return $node;
    }
}
