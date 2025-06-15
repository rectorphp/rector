<?php

declare (strict_types=1);
namespace Rector\Symfony\JMS\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\GenericAnnotationToAttributeConverter;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\JMSAnnotation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\JMS\Rector\Property\AccessorAnnotationToAttributeRector\AccessorAnnotationToAttributeRectorTest
 */
final class AccessorAnnotationToAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private GenericAnnotationToAttributeConverter $genericAnnotationToAttributeConverter;
    public function __construct(DocBlockUpdater $docBlockUpdater, ValueResolver $valueResolver, GenericAnnotationToAttributeConverter $genericAnnotationToAttributeConverter)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->valueResolver = $valueResolver;
        $this->genericAnnotationToAttributeConverter = $genericAnnotationToAttributeConverter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes @Accessor annotation to #[Accessor] attribute with specific "getter" or "setter" keys', [new CodeSample(<<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Accessor;

class User
{
    /**
     * @Accessor("getValue")
     */
    private $value;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Accessor;

class User
{
    #[Accessor(getter: 'getValue')]
    private $value;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Property
    {
        $annotationToAttribute = new AnnotationToAttribute(JMSAnnotation::ACCESSOR);
        $attributeGroup = $this->genericAnnotationToAttributeConverter->convert($node, $annotationToAttribute);
        if (!$attributeGroup instanceof AttributeGroup) {
            return null;
        }
        $attribute = $attributeGroup->attrs[0];
        foreach ($attribute->args as $attributeArg) {
            // already known
            if ($attributeArg->name instanceof Identifier) {
                continue;
            }
            $value = $this->valueResolver->getValue($attributeArg->value);
            if (\strncmp($value, 'get', \strlen('get')) === 0) {
                $attributeArg->name = new Identifier('getter');
            } elseif (\strncmp($value, 'set', \strlen('set')) === 0) {
                $attributeArg->name = new Identifier('setter');
            } else {
                // skip, not getter/setter
                continue;
            }
        }
        // 2. Reprint docblock
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $node->attrGroups = \array_merge($node->attrGroups, [$attributeGroup]);
        return $node;
    }
}
