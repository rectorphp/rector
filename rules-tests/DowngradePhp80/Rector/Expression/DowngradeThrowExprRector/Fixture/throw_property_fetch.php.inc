<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use Symfony\Component\Console\Helper\ProgressBar;

final class ThrowPropertyFetch
{
    public function __construct(
        private ProgressBar|null $progressBar
    ) {
    }

    public function run()
    {
        return $this->progressBar ?? throw new \InvalidArgumentException();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector\Fixture;

use Symfony\Component\Console\Helper\ProgressBar;

final class ThrowPropertyFetch
{
    public function __construct(
        private ProgressBar|null $progressBar
    ) {
    }

    public function run()
    {
        if (!isset($this->progressBar)) {
            throw new \InvalidArgumentException();
        }
        return $this->progressBar;
    }
}

?>
