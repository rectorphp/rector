<?php

declare (strict_types=1);
namespace RectorPrefix202209;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['PhpParser\\Node\\Expr\\ArrayItem' => 'PhpParser\\Node\\ArrayItem', 'PhpParser\\Node\\Expr\\ClosureUse' => 'PhpParser\\Node\\ClosureUse', 'PhpParser\\Node\\Scalar\\EncapsedStringPart' => 'PhpParser\\Node\\InterpolatedStringPart', 'PhpParser\\Node\\Scalar\\LNumber' => 'PhpParser\\Node\\Scalar\\Int_', 'PhpParser\\Node\\Stmt\\DeclareDeclare' => 'PhpParser\\Node\\DeclareItem', 'PhpParser\\Node\\Stmt\\PropertyProperty' => 'PhpParser\\Node\\PropertyItem', 'PhpParser\\Node\\Stmt\\StaticVar' => 'PhpParser\\Node\\StaticVar']);
};
