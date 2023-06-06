<?php

declare (strict_types=1);
namespace Rector\Removing\NodeManipulator;

use PhpParser\Node\Stmt\ClassMethod;
final class ComplexNodeRemover
{
    /**
     * @param int[] $paramKeysToBeRemoved
     * @return int[]
     */
    public function processRemoveParamWithKeys(ClassMethod $classMethod, array $paramKeysToBeRemoved) : array
    {
        $totalKeys = \count($classMethod->params) - 1;
        $removedParamKeys = [];
        foreach ($paramKeysToBeRemoved as $paramKeyToBeRemoved) {
            $startNextKey = $paramKeyToBeRemoved + 1;
            for ($nextKey = $startNextKey; $nextKey <= $totalKeys; ++$nextKey) {
                if (!isset($classMethod->params[$nextKey])) {
                    // no next param, break the inner loop, remove the param
                    break;
                }
                if (\in_array($nextKey, $paramKeysToBeRemoved, \true)) {
                    // keep searching next key not in $paramKeysToBeRemoved
                    continue;
                }
                return [];
            }
            unset($classMethod->params[$paramKeyToBeRemoved]);
            $removedParamKeys[] = $paramKeyToBeRemoved;
        }
        return $removedParamKeys;
    }
}
