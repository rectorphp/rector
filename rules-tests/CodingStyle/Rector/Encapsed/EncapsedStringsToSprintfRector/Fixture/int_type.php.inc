<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class IntType
{
    public function run(array $array, string $item)
    {
        $value = count($array);
        return "We have {$value} {$item}";
    }

    public function run2(string $item)
    {
        $value = 1;
        return "We have {$value} {$item}";
    }

    public function run3(int $value = 1, string $item)
    {
        return "We have {$value} {$item}";
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class IntType
{
    public function run(array $array, string $item)
    {
        $value = count($array);
        return sprintf('We have %d %s', $value, $item);
    }

    public function run2(string $item)
    {
        $value = 1;
        return sprintf('We have %d %s', $value, $item);
    }

    public function run3(int $value = 1, string $item)
    {
        return sprintf('We have %d %s', $value, $item);
    }
}

?>
