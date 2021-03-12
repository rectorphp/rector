<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class JustValue
{
    public function testCommand(): void
    {
        $flag = 'hey';
        $expected = ["--${flag}"];
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class JustValue
{
    public function testCommand(): void
    {
        $flag = 'hey';
        $expected = [sprintf('--%s', $flag)];
    }
}

?>
