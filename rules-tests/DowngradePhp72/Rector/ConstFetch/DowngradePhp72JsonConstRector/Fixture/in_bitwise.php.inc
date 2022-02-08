<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class InBitwise
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class InBitwise
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

?>
