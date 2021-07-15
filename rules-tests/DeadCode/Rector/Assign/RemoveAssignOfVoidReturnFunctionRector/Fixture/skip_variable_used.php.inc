<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

class SkipVariableUsed
{
    public function run()
    {
        if (rand(0, 1)) {
            $value = $this->getOne();
        } else {
            $value = 1;
        }

        return $value;
    }

    public function run2()
    {
        if (rand(0, 1)) {
            $this->value = $this->getOne();
        } else {
            $this->value = 1;
        }

        return $this->value;
    }

    public function run3($value = 1)
    {
        if (rand(0, 1)) {
            $value = $this->getOne();
        }

        return compact('value');
    }

    public function run4($value = 1)
    {
        if (rand(0, 1)) {
            $value = $this->getOne();
        }

        include 'anotherFile.php';
    }

    private function getOne(): void
    {
    }
}

?>
