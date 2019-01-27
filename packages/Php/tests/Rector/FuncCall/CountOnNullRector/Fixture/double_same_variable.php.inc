<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

$fn = function() {
    $primaryColumns = Dibi::getColumns();

    if (count($primaryColumns) === 0) {
        throw new \Exception("x");
    }

    if (count($primaryColumns) > 1) {
        throw new \Exception("y");
    }
};

class Dibi
{
    public static function getColumns(): array
    {
        return [];
    }
}
