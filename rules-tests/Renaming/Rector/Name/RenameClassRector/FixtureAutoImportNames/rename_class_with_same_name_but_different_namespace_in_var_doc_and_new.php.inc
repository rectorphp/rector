<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass;

class RenameClassWithSameNameButDifferentNamespaceInVarDocAndNew
{
    public function run()
    {
        /**
         * @var SomeServiceClass $someService
         */
        $someService = new SomeServiceClass();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;

class RenameClassWithSameNameButDifferentNamespaceInVarDocAndNew
{
    public function run()
    {
        /**
         * @var SomeServiceClass $someService
         */
        $someService = new SomeServiceClass();
    }
}

?>
