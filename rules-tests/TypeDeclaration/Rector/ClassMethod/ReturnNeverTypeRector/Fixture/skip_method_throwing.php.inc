<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

class XY
{
    private function myMethod($string)
    {
        $colParts = preg_split('/\s+/', $string, null, \PREG_SPLIT_NO_EMPTY);
        $numParts = count($colParts);

        if (3 == $numParts) {
        } elseif (2 == $numParts) {
        } elseif (1 == $numParts) {
            if ('*' != $colParts[0]) {
                $this->string($colParts[0]);
            }
        } else {
            throw new Exception(sprintf('"%s" is not valid'));
        }
    }
}
