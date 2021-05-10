<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class SpreadVariablesCollector
{
    /**
     * @return array<int, Param>
     */
    public function resolveFromClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $spreadParams = [];
        foreach ($classMethod->params as $key => $param) {
            // prevent race-condition removal on class method
            $originalParam = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
            if (!$originalParam instanceof \PhpParser\Node\Param) {
                continue;
            }
            if (!$originalParam->variadic) {
                continue;
            }
            $spreadParams[$key] = $param;
        }
        return $spreadParams;
    }
}
