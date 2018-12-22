<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class KnownFloat
{
    public function getFloating()
    {
        if (true) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }

    public function getFloatingParam(float $float)
    {
        return $float;
    }

    public function getFloatingTanks(float $float)
    {
        if (true) {
            return $float;
        }

        return 5.0;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class KnownFloat
{
    public function getFloating(): float
    {
        if (true) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }

    public function getFloatingParam(float $float): float
    {
        return $float;
    }

    public function getFloatingTanks(float $float): float
    {
        if (true) {
            return $float;
        }

        return 5.0;
    }
}

?>
