<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Numberz
{
    public function run(string $format, int $value, float $money)
    {
        return "Format {$format} from {$value} to {$money}";
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Numberz
{
    public function run(string $format, int $value, float $money)
    {
        return sprintf('Format %s from %d to %s', $format, $value, $money);
    }
}

?>
