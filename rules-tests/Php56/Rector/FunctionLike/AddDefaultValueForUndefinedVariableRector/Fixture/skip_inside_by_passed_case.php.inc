<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipInsideByPassedCase
{
    public function run($type)
    {
        switch ($type) {
            case 'a':
            case 'b':
                switch ($type) {
                    case 'a':
                        $value = 'A';
                        break;
                    case 'b':
                        $value = 'B';
                        break;
                }

                return $value;
        }
    }
}
