<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

class HasVoidMethod
{
    public function run(array $values)
    {
        usort($values, [$this, 'thisReturnsVoid']);

        return $values;
    }

    private function thisReturnsVoid(): void
    {
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

class HasVoidMethod
{
    public function run(array $values)
    {
        usort($values, function () : void {
            $this->thisReturnsVoid();
        });

        return $values;
    }

    private function thisReturnsVoid(): void
    {
    }
}

?>
