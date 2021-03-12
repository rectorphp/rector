<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

class Keep
{
    public function run()
    {
        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } else {
            $this->arrayBuilt[][$key2] = $value;
        }

        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } elseif (!empty($value)) {
            $this->arrayBuilt[][$key] = $value;
        }

        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } elseif (!empty($value)) {
            $this->arrayBuilt[][$key] = $value;
        }

        if (empty($value)) {
            $name = true;
        } else {
            $surname = $value;
        }
    }
}
