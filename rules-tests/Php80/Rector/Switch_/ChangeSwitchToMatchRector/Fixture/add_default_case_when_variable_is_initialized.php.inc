<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class AddDefaultCaseWhenVariableIsInitialized
{
    public function getError(int $code) : ?string
    {
        $error = null;
        
        switch ($code) {
            case 10:
                $error = 'code_10';
                break;
            case 11:
                $error = 'code_11';
                break;
        }

        return $error;
    }
}
?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

final class AddDefaultCaseWhenVariableIsInitialized
{
    public function getError(int $code) : ?string
    {        
        $error = match ($code) {
            10 => 'code_10',
            11 => 'code_11',
            default => null,
        };

        return $error;
    }
}
?>
