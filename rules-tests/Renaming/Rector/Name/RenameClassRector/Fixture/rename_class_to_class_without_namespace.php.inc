<?php

namespace MyNamespace;

class AnotherMyClass
{
    public function createSelf(): AnotherMyClass
    {
        return new AnotherMyClass;
    }
}

?>
-----
<?php

class MyNewClassWithoutNamespace
{
    public function createSelf(): \MyNewClassWithoutNamespace
    {
        return new \MyNewClassWithoutNamespace;
    }
}

?>
