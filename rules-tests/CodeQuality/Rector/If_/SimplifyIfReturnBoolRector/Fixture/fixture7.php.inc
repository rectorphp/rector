<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

trait ValidatesAttributes
{
    public function validateDimensions()
    {
        if (true || true) {
            return false;
        }

        return true;
    }

    public function function2($value, $secondValue)
    {
        if (($value === true) || ($secondValue === true)) {
            return false;
        }

        return true;
    }

    public function function3($attribute, $value, $parameters)
    {
        if ($this->failsBasicDimensionChecks($parameters, $width, $height) ||
            $this->failsRatioCheck($parameters, $width, $height)) {
            return false;
        }

        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

trait ValidatesAttributes
{
    public function validateDimensions()
    {
        return !(true || true);
    }

    public function function2($value, $secondValue)
    {
        return !(($value === true) || ($secondValue === true));
    }

    public function function3($attribute, $value, $parameters)
    {
        return !($this->failsBasicDimensionChecks($parameters, $width, $height) ||
            $this->failsRatioCheck($parameters, $width, $height));
    }
}

?>
