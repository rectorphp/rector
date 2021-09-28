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
        $item2Unpacked = $this->getArray();
        switch ($operation) {
            case 'fruits':
                return array_merge(['banana', 'orange'], $item2Unpacked);
        }
    }
}

?>
