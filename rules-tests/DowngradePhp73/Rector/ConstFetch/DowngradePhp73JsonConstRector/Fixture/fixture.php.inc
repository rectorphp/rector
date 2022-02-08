<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradePhp73JsonConstRector\Fixture;

class Fixture
{
    public function run()
    {
        $inDecoder = new Decoder($connection, true, 512, \JSON_THROW_ON_ERROR);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradePhp73JsonConstRector\Fixture;

class Fixture
{
    public function run()
    {
        $inDecoder = new Decoder($connection, true, 512, 0);
    }
}

?>
