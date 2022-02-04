<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class Fixture
{
    public function run()
    {
        $inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_IGNORE);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class Fixture
{
    public function run()
    {
        $inDecoder = new Decoder($connection, true, 512, 0);
    }
}

?>
