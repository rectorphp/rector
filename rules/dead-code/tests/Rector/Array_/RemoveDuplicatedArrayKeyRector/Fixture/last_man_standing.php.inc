<?php

namespace Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector\Fixture;

class LastManStanding
{
    public function lists()
    {
        $items = [
            1 => 'A',
            1 => 'B',
            1 => 'C'
        ];

        $items = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            1 => 'E',
            2 => 'F',
            3 => 'G',
            4 => 'H',
        ];
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector\Fixture;

class LastManStanding
{
    public function lists()
    {
        $items = [
            1 => 'C'
        ];

        $items = [
            1 => 'E',
            2 => 'F',
            3 => 'G',
            4 => 'H',
        ];
    }
}

?>
