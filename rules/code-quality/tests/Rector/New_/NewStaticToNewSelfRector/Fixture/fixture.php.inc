<?php

namespace Rector\CodeQuality\Tests\Rector\New_\NewStaticToNewSelfRector\Fixture;

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

namespace Rector\CodeQuality\Tests\Rector\New_\NewStaticToNewSelfRector\Fixture;

final class Fixture
{
    public function build()
    {
        return new self();
    }
}

?>
