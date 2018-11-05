<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRetor\Wrong;

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
