<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class Fixture6
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

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class Fixture6
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
