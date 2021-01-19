<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class WithoutReturn
{
    public function run($value)
    {
        switch ($value) {
            case 1:
                $this->x($value);
                break;

            default:
                $this->y($value);
                break;
        }
    }
    
    private function x($value) {}
    private function y($value) {}
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class WithoutReturn
{
    public function run($value)
    {
        match ($value) {
            1 => $this->x($value),
            default => $this->y($value),
        };
    }
    
    private function x($value) {}
    private function y($value) {}
}

?>
