<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser\Node\Expr\Cast;

use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
class Object_ extends Cast
{
    public function getType() : string
    {
        return 'Expr_Cast_Object';
    }
}
