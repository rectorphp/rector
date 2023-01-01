<?php

declare (strict_types=1);
namespace RectorPrefix202301;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
/**
 * @see https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md
 */
return static function (RectorConfig $rectorConfig) : void {
    // https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md#renamed-nodes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['PhpParser\\Node\\Scalar\\LNumber' => 'PhpParser\\Node\\Scalar\\Int_', 'PhpParser\\Node\\Scalar\\DNumber' => 'PhpParser\\Node\\Scalar\\Float_', 'PhpParser\\Node\\Scalar\\Encapsed' => 'PhpParser\\Node\\Scalar\\InterpolatedString', 'PhpParser\\Node\\Scalar\\EncapsedStringPart' => 'PhpParser\\Node\\InterpolatedStringPart', 'PhpParser\\Node\\Expr\\ArrayItem' => 'PhpParser\\Node\\ArrayItem', 'PhpParser\\Node\\Expr\\ClosureUse' => 'PhpParser\\Node\\ClosureUse', 'PhpParser\\Node\\Stmt\\DeclareDeclare' => 'PhpParser\\Node\\DeclareItem', 'PhpParser\\Node\\Stmt\\PropertyProperty' => 'PhpParser\\Node\\PropertyItem', 'PhpParser\\Node\\Stmt\\StaticVar' => 'PhpParser\\Node\\StaticVar', 'PhpParser\\Node\\Stmt\\UseUse' => 'PhpParser\\Node\\UseItem']);
    // https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md#modifiers
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_PUBLIC', 'PhpParser\\Modifiers', 'PUBLIC'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_PUBLIC', 'PhpParser\\Modifiers', 'PUBLIC'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_PROTECTED', 'PhpParser\\Modifiers', 'PROTECTED'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_PRIVATE', 'PhpParser\\Modifiers', 'PRIVATE'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_STATIC', 'PhpParser\\Modifiers', 'STATIC'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_ABSTRACT', 'PhpParser\\Modifiers', 'ABSTRACT'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_FINAL', 'PhpParser\\Modifiers', 'FINAL'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'MODIFIER_READONLY', 'PhpParser\\Modifiers', 'READONLY'), new RenameClassAndConstFetch('PhpParser\\Node\\Stmt\\Class_', 'VISIBILITY_MODIFIER_MASK', 'PhpParser\\Modifiers', 'VISIBILITY_MASK')]);
    // https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md#other-removed-functionality
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('PhpParser\\Node\\Expr\\MethodCall', 'setTypeHint', 'setType')]);
};
