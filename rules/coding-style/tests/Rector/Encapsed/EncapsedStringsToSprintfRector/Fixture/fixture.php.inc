<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Fixture
{
    public function run(string $format)
    {
        return "Unsupported format {$format}";
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Fixture
{
    public function run(string $format)
    {
        return sprintf('Unsupported format %s', $format);
    }
}

?>
