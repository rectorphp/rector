<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\NodeFactory;

use PhpParser\Node\AttributeGroup;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PHPUnit\AnnotationsToAttributes\ValueObject\RequiresAttributeAndValue;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
final class RequiresAttributeFactory
{
    /**
     * @readonly
     */
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
    }
    public function create(string $annotationValue): ?AttributeGroup
    {
        $annotationValues = explode(' ', $annotationValue, 2);
        $type = array_shift($annotationValues);
        $attributeValue = array_shift($annotationValues);
        $requiresAttributeAndValue = $this->matchRequiresAttributeAndValue($type, $attributeValue);
        if (!$requiresAttributeAndValue instanceof RequiresAttributeAndValue) {
            return null;
        }
        return $this->phpAttributeGroupFactory->createFromClassWithItems($requiresAttributeAndValue->getAttributeClass(), $requiresAttributeAndValue->getValue());
    }
    /**
     * @param mixed $attributeValue
     */
    private function matchRequiresAttributeAndValue(string $type, $attributeValue): ?RequiresAttributeAndValue
    {
        if ($type === 'PHP') {
            // only version is used, we need to prefix with >=
            if (is_string($attributeValue) && is_numeric($attributeValue[0])) {
                $attributeValue = '>= ' . $attributeValue;
            }
            return new RequiresAttributeAndValue(PHPUnitAttribute::REQUIRES_PHP, [$attributeValue]);
        }
        if ($type === 'PHPUnit') {
            // only version is used, we need to prefix with >=
            if (is_string($attributeValue) && is_numeric($attributeValue[0])) {
                $attributeValue = '>= ' . $attributeValue;
            }
            return new RequiresAttributeAndValue(PHPUnitAttribute::REQUIRES_PHPUNIT, [$attributeValue]);
        }
        if ($type === 'OS') {
            return new RequiresAttributeAndValue(PHPUnitAttribute::REQUIRES_OS, [$attributeValue]);
        }
        if ($type === 'OSFAMILY') {
            return new RequiresAttributeAndValue(PHPUnitAttribute::REQUIRES_OS_FAMILY, [$attributeValue]);
        }
        if ($type === 'function') {
            if (strpos((string) $attributeValue, '::') !== \false) {
                $attributeClass = PHPUnitAttribute::REQUIRES_METHOD;
                $attributeValue = explode('::', (string) $attributeValue);
                $attributeValue[0] .= '::class';
            } else {
                $attributeClass = PHPUnitAttribute::REQUIRES_FUNCTION;
                $attributeValue = [$attributeValue];
            }
            return new RequiresAttributeAndValue($attributeClass, $attributeValue);
        }
        if ($type === 'extension') {
            return new RequiresAttributeAndValue(PHPUnitAttribute::REQUIRES_PHP_EXTENSION, explode(' ', (string) $attributeValue, 2));
        }
        if ($type === 'setting') {
            return new RequiresAttributeAndValue(PHPUnitAttribute::REQUIRES_SETTING, explode(' ', (string) $attributeValue, 2));
        }
        return null;
    }
}
