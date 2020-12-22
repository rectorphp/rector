<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class Fixture extends \PHPUnit_Framework_TestCase
{
    public function create(\PHPUnit_Something $argument): \PHPUnit_Framework_Something
    {
        return new \PHPUnit_Framework_Something;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class Fixture extends \PHPUnit\Framework\TestCase
{
    public function create(\PHPUnit\Something $argument): \PHPUnit\Framework\Something
    {
        return new \PHPUnit\Framework\Something;
    }
}

?>
