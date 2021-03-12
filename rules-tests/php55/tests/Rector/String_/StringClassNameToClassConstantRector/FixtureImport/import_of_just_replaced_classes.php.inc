<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

class ImportOfJustReplacedClasses
{
    public function run()
    {
        $class = 'Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\SomeUser';
        $user = new $class;

        $anotherClass = 'Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\SomeSecurity\SomeUser';
        $user = new $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\FixtureImport;

use Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\SomeUser;
class ImportOfJustReplacedClasses
{
    public function run()
    {
        $class = SomeUser::class;
        $user = new $class;

        $anotherClass = \Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Source\SomeSecurity\SomeUser::class;
        $user = new $anotherClass;
    }
}

?>
