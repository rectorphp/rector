<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass;

class SomeClass3
{
    public function run()
    {
        /**
         * @var SomeServiceClass $someService
         */
        $someService = get_service();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;

class SomeClass3
{
    public function run()
    {
        /**
         * @var SomeServiceClass $someService
         */
        $someService = get_service();
    }
}

?>
