<?php

namespace OldNamespace\SubNamespace;

class SomeTest extends \PHPUnit_Framework_TestCase
{
    public function create(\PHPUnit_Something $argument): \PHPUnit_Framework_Something
    {
        return new \PHPUnit_Framework_Something;
    }
}

?>
-----
<?php

namespace OldNamespace\SubNamespace;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function create(\PHPUnit\Something $argument): \PHPUnit\Framework\Something
    {
        return new \PHPUnit\Framework\Something;
    }
}

?>
