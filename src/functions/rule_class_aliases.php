<?php

declare (strict_types=1);
namespace RectorPrefix202610;

use Rector\DeadCode\Rector\Class_\RemoveParentDelegatingClassMethodRector;
use Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromMockObjectRector;
use Rector\TypeDeclaration\Rector\ClassLike\AddVoidReturnTypeWhereNoReturnRector;
use Rector\Visibility\Rector\Class_\ChangeMethodVisibilityRector;
// backward-compatibility aliases for rules retargeted to Class_/ClassLike nodes and moved out of the ClassMethod namespace,
// so configs and 3rd party sets referencing the former namespace keep working
$legacyToCurrentRuleClasses = ['Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector' => ChangeMethodVisibilityRector::class, 'Rector\DeadCode\Rector\ClassMethod\RemoveParentDelegatingClassMethodRector' => RemoveParentDelegatingClassMethodRector::class, 'Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromMockObjectRector' => ReturnTypeFromMockObjectRector::class, 'Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector' => AddVoidReturnTypeWhereNoReturnRector::class];
// register lazily, so the current rule classes are only loaded when the legacy name is actually requested
\spl_autoload_register(static function (string $class) use ($legacyToCurrentRuleClasses): void {
    if (isset($legacyToCurrentRuleClasses[$class])) {
        \class_alias($legacyToCurrentRuleClasses[$class], $class);
    }
});
