<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use Symfony\Component\Console\Helper\ProgressBar;

final class ReturnThrowVariable
{
    public function run()
    {
        return $variable ?? throw new \InvalidArgumentException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use Symfony\Component\Console\Helper\ProgressBar;

final class ReturnThrowVariable
{
    public function run()
    {
        if (!isset($variable)) {
            throw new \InvalidArgumentException();
        }
        return $variable;
    }
}

?>
