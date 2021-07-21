<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

final class NoAssignInConstruct
{
    private string $name;

    public function __construct()
    {
    }

    public function sayHello(string $aName)
    {
        $this->name = $aName;
        return "Hello {$this->name}";
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

final class NoAssignInConstruct
{
    public function __construct()
    {
    }

    public function sayHello(string $aName)
    {
        $name = $aName;
        return "Hello {$name}";
    }
}

?>
