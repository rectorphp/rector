<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FunctionExistsFunCallAnalyzer
{
    public function detect(FuncCall $funcCall, string $functionName) : bool
    {
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        return $scope->isInFunctionExists($functionName);
    }
}
