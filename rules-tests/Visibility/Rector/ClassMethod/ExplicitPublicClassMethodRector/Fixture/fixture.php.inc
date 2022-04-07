<?php

class SomeClass
{
    function noExplicitVis()
    {
        $a = 3;
        $closure = function ($b) use ($a) {
            return $a + $b;
        };
        return $closure;
    }

    public function publicVis()
    {
    }

    protected function protectedVis()
    {
    }

    private function privateVis()
    {
    }
}

function notInScope()
{
}

?>
-----
<?php

class SomeClass
{
    public function noExplicitVis()
    {
        $a = 3;
        $closure = function ($b) use ($a) {
            return $a + $b;
        };
        return $closure;
    }

    public function publicVis()
    {
    }

    protected function protectedVis()
    {
    }

    private function privateVis()
    {
    }
}

function notInScope()
{
}

?>
