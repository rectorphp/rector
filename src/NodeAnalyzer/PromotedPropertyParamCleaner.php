<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Param;
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
