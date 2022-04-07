<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class DirectSwitchCaseNoDefault
{
    public function run($type)
    {
        switch ($type) {
            case 'a':
                $value = 'A';
                break;
            case 'b':
                $value = 'B';
                break;
        }

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class DirectSwitchCaseNoDefault
{
    public function run($type)
    {
        $value = null;
        switch ($type) {
            case 'a':
                $value = 'A';
                break;
            case 'b':
                $value = 'B';
                break;
        }

        return $value;
    }
}

?>
