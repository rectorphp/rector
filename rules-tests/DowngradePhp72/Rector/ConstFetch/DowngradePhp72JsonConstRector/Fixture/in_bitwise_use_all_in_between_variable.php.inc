<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class InBitwiseUseAllInBetweenVariable
{
    public function run($argument, $variable)
    {
        $argument = json_encode($argument, JSON_INVALID_UTF8_IGNORE | $variable | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePhp72JsonConstRector\Fixture;

class InBitwiseUseAllInBetweenVariable
{
    public function run($argument, $variable)
    {
        $argument = json_encode($argument, $variable);
    }
}

?>
