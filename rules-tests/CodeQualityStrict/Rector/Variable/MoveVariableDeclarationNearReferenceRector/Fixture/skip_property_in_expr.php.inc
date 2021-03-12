<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipPropertyInExpr
{
    function myMethod()
    {
        $var = $this->run($this->a->b, $this->c->d);

        if (true) {
            $this->a = new \stdClass;
            $this->b = new \stdClass;

            echo $var;
        }
    }
}

?>
