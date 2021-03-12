<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class SkipDoubleSameVariable
{
    public function run()
    {
        $fn = function() {
            $primaryColumns = Dibi::getColumns();

            if (count($primaryColumns) === 0) {
                throw new \Exception("x");
            }

            if (count($primaryColumns) > 1) {
                throw new \Exception("y");
            }
        };
    }
}


class Dibi
{
    public static function getColumns(): array
    {
        return [];
    }
}
