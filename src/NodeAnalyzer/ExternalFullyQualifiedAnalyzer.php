<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ExternalFullyQualifiedAnalyzer
{
    /**
     * Is in a class that depends on a class, interface or trait located in vendor?
     */
    public function hasVendorLocatedDependency(\PhpParser\Node $node) : bool
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($classReflection === $ancestorClassReflection) {
                continue;
            }
            $fileName = $ancestorClassReflection->getFileName();
            if ($fileName === \false) {
                continue;
            }
            // file is located in vendor → out of modifiable scope
            if (\strpos($fileName, '/vendor/') !== \false) {
                return \true;
            }
        }
        return \false;
    }
}
