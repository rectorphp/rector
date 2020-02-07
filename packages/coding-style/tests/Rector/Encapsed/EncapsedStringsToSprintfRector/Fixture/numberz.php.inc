<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Numberz
{
    public function run(string $format, int $value, float $money)
    {
        return sprintf('Format %s from %s to %s', $format, $value, $money);
    }
}

?>
