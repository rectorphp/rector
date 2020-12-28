<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\Fixture;

class GlobalAfter
{
    public function go()
    {
        $variable = 3;
        global $variable;
        $variable = 5;
        return $variable;
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

namespace Rector\Privatization\Tests\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\Fixture;

class GlobalAfter
{
    private $variable;
    public function go()
    {
        $variable = 3;
        $this->variable = 5;
        return $this->variable;
    }

    public function run()
    {
        var_dump($this->variable);
    }
}

?>
