<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst;

use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst;
class Trait_ extends MagicConst
{
    public function getName() : string
    {
        return '__TRAIT__';
    }
    public function getType() : string
    {
        return 'Scalar_MagicConst_Trait';
    }
}
