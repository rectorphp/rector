<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = 'yes';

        compact('value', 'non_existing');
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = 'yes';

        compact('value');
    }
}

?>
