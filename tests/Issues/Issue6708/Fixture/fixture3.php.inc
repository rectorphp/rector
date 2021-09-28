<?php

namespace Rector\Core\Tests\Issues\Issue6708\Fixture;

class Fixture3
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run($operation)
    {
        return match ($operation) {
            'fruits' => ['banana', 'orange', ...$this->getFirstArray()],
            'veggies' => ['tomato', 'lettuce', ...$this->getSecondArray()],
        };
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6708\Fixture;

class Fixture3
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run($operation)
    {
        $item2Unpacked = $this->getFirstArray();
        $item3Unpacked = $this->getSecondArray();
        switch ($operation) {
            case 'fruits':
                return array_merge(['banana', 'orange'], is_array($item2Unpacked) ? $item2Unpacked : iterator_to_array($item2Unpacked));
            case 'veggies':
                return array_merge(['tomato', 'lettuce'], is_array($item3Unpacked) ? $item3Unpacked : iterator_to_array($item3Unpacked));
        }
    }
}

?>
