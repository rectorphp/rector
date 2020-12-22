<?php

class Fixture4
{
    public function someClass()
    {
        return new \PHPUnit_Framework_Something;
    }
}

?>
-----
<?php

class Fixture4
{
    public function someClass()
    {
        return new \PHPUnit\Framework\Something;
    }
}

?>
