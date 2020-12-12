<?php

namespace OldNamespace\SubNamespace;

class VarDoc
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    public $test;
}

?>
-----
<?php

namespace OldNamespace\SubNamespace;

class VarDoc
{
    /**
     * @var \PHPUnit\Framework\TestCase
     */
    public $test;
}

?>
