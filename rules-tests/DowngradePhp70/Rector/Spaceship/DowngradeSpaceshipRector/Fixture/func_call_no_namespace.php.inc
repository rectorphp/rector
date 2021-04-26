<?php

function getRand3()
{
    return rand(1,100);
}

function getRand4()
{
    return rand(1,100);
}

class FuncCallNoNamespace
{
    public function run()
    {
        return getRand3() <=> getRand4();
    }
}

?>
-----
<?php

function getRand3()
{
    return rand(1,100);
}

function getRand4()
{
    return rand(1,100);
}

class FuncCallNoNamespace
{
    public function run()
    {
        return (function ($getRand3, $getRand4) {
            if ($getRand3 === $getRand4) {
                return 0;
            }
            return $getRand3 < $getRand4 ? -1 : 1;
        })(getRand3(), getRand4());
    }
}

?>
