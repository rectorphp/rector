<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

class NoReturnForVoid
{
    public function run(array $values)
    {
        usort($values, [$this, 'thisReturnsVoid']);
        usort($values, [$this, 'thisReturnsNothing']);

        return $values;
    }

    private function thisReturnsVoid(): void
    {
    }

    private function thisReturnsNothing(): void
    {
        return;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

class NoReturnForVoid
{
    public function run(array $values)
    {
        usort($values, function () : void {
            $this->thisReturnsVoid();
        });
        usort($values, function () : void {
            $this->thisReturnsNothing();
        });

        return $values;
    }

    private function thisReturnsVoid(): void
    {
    }

    private function thisReturnsNothing(): void
    {
        return;
    }
}

?>
