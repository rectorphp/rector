<?php

namespace Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class AnonymousFunction
{
    public function run()
    {
        $lines = [];

        return array_map(static function ($line) {
            [$value1, $value2] = explode("\t", $line);

            return [
                'value_1' => $value1,
                'value_2' => $value2,
            ];
        }, $lines);
    }
}
