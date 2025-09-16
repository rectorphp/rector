<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\CodeQuality\ValueObject\DefinedPropertyWithType;
use Rector\NodeAnalyzer\PropertyPresenceChecker;
final class MissingPropertiesResolver
{
    /**
     * @readonly
     */
    private \Rector\CodeQuality\NodeAnalyzer\ClassLikeAnalyzer $classLikeAnalyzer;
    /**
     * @readonly
     */
    private PropertyPresenceChecker $propertyPresenceChecker;
    public function __construct(\Rector\CodeQuality\NodeAnalyzer\ClassLikeAnalyzer $classLikeAnalyzer, PropertyPresenceChecker $propertyPresenceChecker)
    {
        $this->classLikeAnalyzer = $classLikeAnalyzer;
        $this->propertyPresenceChecker = $propertyPresenceChecker;
    }
    /**
     * @param DefinedPropertyWithType[] $definedPropertiesWithTypes
     * @return DefinedPropertyWithType[]
     */
    public function resolve(Class_ $class, ClassReflection $classReflection, array $definedPropertiesWithTypes): array
    {
        $existingPropertyNames = $this->classLikeAnalyzer->resolvePropertyNames($class);
        $missingPropertiesWithTypes = [];
        foreach ($definedPropertiesWithTypes as $definedPropertyWithType) {
            // 1. property already exists, skip it
            if (in_array($definedPropertyWithType->getName(), $existingPropertyNames, \true)) {
                continue;
            }
            // 2. is part of class docblock or another magic, skip it
            if ($classReflection->hasInstanceProperty($definedPropertyWithType->getName())) {
                continue;
            }
            // 3. is fetched by parent class on non-private property etc., skip it
            $hasClassContextProperty = $this->propertyPresenceChecker->hasClassContextProperty($class, $definedPropertyWithType);
            if ($hasClassContextProperty) {
                continue;
            }
            // it's most likely missing!
            $missingPropertiesWithTypes[] = $definedPropertyWithType;
        }
        return $missingPropertiesWithTypes;
    }
}
