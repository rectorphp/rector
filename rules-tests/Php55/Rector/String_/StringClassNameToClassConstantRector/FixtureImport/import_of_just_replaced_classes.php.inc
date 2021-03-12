<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

class ImportOfJustReplacedClasses
{
    public function run()
    {
        $class = 'Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\SomeUser';
        $user = new $class;

        $anotherClass = 'Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\SomeSecurity\SomeUser';
        $user = new $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\SomeUser;
class ImportOfJustReplacedClasses
{
    public function run()
    {
        $class = SomeUser::class;
        $user = new $class;

        $anotherClass = \Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Source\SomeSecurity\SomeUser::class;
        $user = new $anotherClass;
    }
}

?>
