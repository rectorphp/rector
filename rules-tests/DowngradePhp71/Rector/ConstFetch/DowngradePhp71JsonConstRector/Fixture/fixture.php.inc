<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradePhp71JsonConstRector\Fixture;

class Fixture
{
    public function run()
    {
        $inDecoder = new Decoder($connection, true, 512, \JSON_UNESCAPED_LINE_TERMINATORS);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradePhp71JsonConstRector\Fixture;

class Fixture
{
    public function run()
    {
        $inDecoder = new Decoder($connection, true, 512, 0);
    }
}

?>
