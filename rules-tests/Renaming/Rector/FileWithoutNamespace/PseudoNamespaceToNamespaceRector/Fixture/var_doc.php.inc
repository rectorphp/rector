<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

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

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class VarDoc
{
    /**
     * @var \PHPUnit\Framework\TestCase
     */
    public $test;
}

?>
