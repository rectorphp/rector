<?php

namespace Rector\NetteUtilsCodeQuality\Tests\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector\Fixture;

final class Fixture
{
    public function run()
    {
        return 86400;
    }
}

?>
-----
<?php

namespace Rector\NetteUtilsCodeQuality\Tests\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector\Fixture;

final class Fixture
{
    public function run()
    {
        return \Nette\Utils\DateTime::DAY;
    }
}

?>
