<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Enum\ClassName;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Util\StringUtils;
final class JMSTypeAnalyzer
{
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(AttributeFinder $attributeFinder, PhpAttributeAnalyzer $phpAttributeAnalyzer, ValueResolver $valueResolver)
    {
        $this->attributeFinder = $attributeFinder;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function hasAtLeastOneUntypedPropertyUsingJmsAttribute(Class_ $class): bool
    {
        foreach ($class->getProperties() as $property) {
            if ($property->type instanceof Node) {
                continue;
            }
            if ($this->attributeFinder->hasAttributeByClasses($property, [ClassName::JMS_TYPE])) {
                return \true;
            }
        }
        return \false;
    }
    public function hasPropertyJMSTypeAttribute(Property $property): bool
    {
        if (!$this->phpAttributeAnalyzer->hasPhpAttribute($property, ClassName::JMS_TYPE)) {
            return \false;
        }
        // most likely collection, not sole type
        return !$this->phpAttributeAnalyzer->hasPhpAttributes($property, array_merge(CollectionMapping::TO_MANY_CLASSES, CollectionMapping::TO_ONE_CLASSES));
    }
    public function resolveTypeAttributeValue(Property $property): ?string
    {
        $jmsTypeAttribute = $this->attributeFinder->findAttributeByClass($property, ClassName::JMS_TYPE);
        if (!$jmsTypeAttribute instanceof Attribute) {
            return null;
        }
        $typeValue = $this->valueResolver->getValue($jmsTypeAttribute->args[0]->value);
        if (!is_string($typeValue)) {
            return null;
        }
        if (StringUtils::isMatch($typeValue, '#DateTime\<(.*?)\>#')) {
            // special case for DateTime, which is not a scalar type
            return 'DateTime';
        }
        return $typeValue;
    }
}
