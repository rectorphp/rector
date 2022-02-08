<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradePhp71JsonConstRector\Fixture;

class InBitwise
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp71\Rector\FuncCall\DowngradePhp71JsonConstRector\Fixture;

class InBitwise
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

?>
