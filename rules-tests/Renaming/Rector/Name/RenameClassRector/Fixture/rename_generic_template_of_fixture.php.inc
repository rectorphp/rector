<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use MyNamespace\MyClass;

/**
 * @template T of MyClass
 */
interface MyServiceInterface
{
    /**
     * @return T
     */
    public function get(): MyClass;
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use MyNamespace\MyClass;

/**
 * @template T of \MyNewNamespace\MyNewClass
 */
interface MyServiceInterface
{
    /**
     * @return T
     */
    public function get(): \MyNewNamespace\MyNewClass;
}

?>
