<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradePhp73JsonConstRector\Fixture;

class InBitwise
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradePhp73JsonConstRector\Fixture;

class InBitwise
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_UNESCAPED_SLASHES);
    }
}

?>
