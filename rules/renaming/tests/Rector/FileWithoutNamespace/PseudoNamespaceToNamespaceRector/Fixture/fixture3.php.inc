<?php

class PHPUnit_Abc
{
    public function run()
    {
        return 1;
    }
}
?>
-----
<?php

namespace PHPUnit;

class Abc
{
    public function run()
    {
        return 1;
    }
}
?>