<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRetor\Wrong;

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
