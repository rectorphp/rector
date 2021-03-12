<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Fixture2
{
    private $format = 'json';

    public function run(string $format)
    {
        return "Unsupported format {$this->format}";
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector\Fixture;

final class Fixture2
{
    private $format = 'json';

    public function run(string $format)
    {
        return sprintf('Unsupported format %s', $this->format);
    }
}

?>
