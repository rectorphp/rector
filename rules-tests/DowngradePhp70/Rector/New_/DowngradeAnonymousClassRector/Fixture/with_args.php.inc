<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class WithArgs
{
    public function run($arg1, $arg2)
    {
        return new class ($arg1, $arg2) {
            public function execute()
            {
            }
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class Anonymous
{
    public function execute()
    {
    }
}
class WithArgs
{
    public function run($arg1, $arg2)
    {
        return new Anonymous($arg1, $arg2);
    }
}

?>