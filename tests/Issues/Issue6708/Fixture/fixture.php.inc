<?php

namespace Rector\Core\Tests\Issues\Issue6708\Fixture;

class Fixture
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run($operation)
    {
        return match ($operation) {
            'fruits' => ['banana', 'orange', ...$this->getArray()],
        };
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6708\Fixture;

class Fixture
{
    function getArray(): array
    {
        return ['apple', 'pear'];
    }

    public function run($operation)
    {
        switch ($operation) {
            case 'fruits':
                $item2Unpacked = $this->getArray();
                return array_merge(['banana', 'orange'], $item2Unpacked);
        }
    }
}

?>
