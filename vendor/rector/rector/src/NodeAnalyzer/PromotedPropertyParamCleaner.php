<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Param;
final class PromotedPropertyParamCleaner
{
    /**
     * @param Param[] $params
     * @return Param[]
     */
    public function cleanFromFlags(array $params) : array
    {
        $cleanParams = [];
        foreach ($params as $param) {
            $cleanParam = clone $param;
            $cleanParam->flags = 0;
            $cleanParams[] = $cleanParam;
        }
        return $cleanParams;
    }
}
