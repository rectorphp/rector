<?php

class SomeClass4
{
    public function someClass()
    {
        return new \PHPUnit_Framework_Something;
    }
}

?>
-----
<?php

class SomeClass4
{
    public function someClass()
    {
        return new \PHPUnit\Framework\Something;
    }
}

?>
