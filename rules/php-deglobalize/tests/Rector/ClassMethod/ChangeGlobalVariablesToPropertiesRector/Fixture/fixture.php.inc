<?php

namespace Rector\PhpDeglobalize\Tests\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\Fixture;

class SomeClass
{
    public function go()
    {
        global $variable;
        $variable = 5;
    }

    public function run()
    {
        global $variable;
        var_dump($variable);
    }
}

?>
-----
<?php

namespace Rector\PhpDeglobalize\Tests\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\Fixture;

class SomeClass
{
    private $variable;
    public function go()
    {
        $this->variable = 5;
    }

    public function run()
    {
        var_dump($this->variable);
    }
}

?>
