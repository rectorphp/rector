<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

final class InterVariable
{
    public function run(string $variable)
    {
        require 'path/' . $variable;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

final class InterVariable
{
    public function run(string $variable)
    {
        require __DIR__ . '/path/' . $variable;
    }
}

?>
