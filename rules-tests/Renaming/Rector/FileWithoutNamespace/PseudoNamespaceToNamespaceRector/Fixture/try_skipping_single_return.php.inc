<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

use PHPUnit_TestCase;
use Rector\Tests\Arguments\Rector\Name\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class SomeTestCase
{
    /**
     * @return \ChangeMe_AnotherNamespace
     */
    public function someMethod(): Keep_This
    {
        if ($this instanceof PHPUnit_TestCase) {
            return true;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

use PHPUnit\TestCase;
use Rector\Tests\Arguments\Rector\Name\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class SomeTestCase
{
    /**
     * @return \ChangeMe\AnotherNamespace
     */
    public function someMethod(): Keep_This
    {
        if ($this instanceof \PHPUnit\TestCase) {
            return true;
        }
    }
}

?>
