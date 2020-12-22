<?php

namespace Old\Long\AnyNamespace;

class SomeClass5
{
    public function someClass()
    {
        return new SomeClass5;
    }
}

?>
-----
<?php

namespace Short\AnyNamespace;

class SomeClass5
{
    public function someClass()
    {
        return new \Short\AnyNamespace\SomeClass5;
    }
}

?>
