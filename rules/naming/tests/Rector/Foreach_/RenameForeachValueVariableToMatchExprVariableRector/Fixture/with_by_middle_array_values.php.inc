<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddleArrayValues
{
    public function run()
    {
        $methodsByClass = $this->getData();
        foreach ($methodsByClass as $property) {
            $array[] = $property;
        }
    }

    private function getData()
    {
        return [
            'a' => [
                [

                ]
            ]
        ];
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class WithByMiddleArrayValues
{
    public function run()
    {
        $methodsByClass = $this->getData();
        foreach ($methodsByClass as $methods) {
            $array[] = $methods;
        }
    }

    private function getData()
    {
        return [
            'a' => [
                [

                ]
            ]
        ];
    }
}

?>
