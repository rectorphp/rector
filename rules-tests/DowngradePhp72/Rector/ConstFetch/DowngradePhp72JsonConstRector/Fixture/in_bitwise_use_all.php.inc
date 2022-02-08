<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class InBitwiseUseAll
{
    public function run($argument)
    {
        $argument = json_encode($argument, JSON_INVALID_UTF8_IGNORE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class InBitwiseUseAll
{
    public function run($argument)
    {
        $argument = json_encode($argument, 0);
    }
}

?>
