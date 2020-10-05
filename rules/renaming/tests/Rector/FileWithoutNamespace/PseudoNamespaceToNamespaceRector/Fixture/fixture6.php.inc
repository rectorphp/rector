<?php

namespace OldNamespace\SubNamespace;

class AnnotationsTest
{
    /**
     * @param \PHPUnit_Framework_TestCase $test
     * @return \PHPUnit_Framework_TestCase
     * @throws \PHPUnit_Framework_TestCase
     */
    public function test(\PHPUnit_Framework_TestCase $test): \PHPUnit_Framework_TestCase
    {
    }
}

?>
-----
<?php

namespace OldNamespace\SubNamespace;

class AnnotationsTest
{
    /**
     * @param \PHPUnit\Framework\TestCase $test
     * @return \PHPUnit\Framework\TestCase
     * @throws \PHPUnit\Framework\TestCase
     */
    public function test(\PHPUnit\Framework\TestCase $test): \PHPUnit\Framework\TestCase
    {
    }
}

?>
