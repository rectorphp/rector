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
        switch ($operation) {
            case 'fruits':
                $item2Unpacked = $this->getFirstArray();
                return array_merge(['banana', 'orange'], is_array($item2Unpacked) ? $item2Unpacked : iterator_to_array($item2Unpacked));
            case 'veggies':
                $item2Unpacked = $this->getSecondArray();
                return array_merge(['tomato', 'lettuce'], is_array($item2Unpacked) ? $item2Unpacked : iterator_to_array($item2Unpacked));
        }
    }
}

?>
