<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass;

class SomeClass4
{
    public function run()
    {
        /**
         * @var string $someVariable
         */
        $someVariable = getValue();

        /**
         * @var SomeServiceClass $someService
         */
        $someService2 = get_service2();

        /**
         * @var SomeServiceClass $someService
         */
        $someService3 = get_service2();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;

class SomeClass4
{
    public function run()
    {
        /**
         * @var string $someVariable
         */
        $someVariable = getValue();

        /**
         * @var SomeServiceClass $someService
         */
        $someService2 = get_service2();

        /**
         * @var SomeServiceClass $someService
         */
        $someService3 = get_service2();
    }
}

?>
