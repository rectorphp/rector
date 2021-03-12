<?php

namespace Rector\Tests\CodeQuality\Rector\New_\NewStaticToNewSelfRector\Fixture;

final class Fixture
{
    public function build()
    {
        return new static();
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\New_\NewStaticToNewSelfRector\Fixture;

final class Fixture
{
    public function build()
    {
        return new self();
    }
}

?>
