<?php

namespace Rector\Tests\DowngradePhp70\Rector\Class_\DowngradeAnonymousClassRector\Fixture;

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

namespace Rector\Tests\DowngradePhp70\Rector\Class_\DowngradeAnonymousClassRector\Fixture;

class WithArgs
{
    public function run($arg1, $arg2)
    {
        return new Anonymous($arg1, $arg2);
    }
}
class Anonymous
{
    public function execute()
    {
    }
}

?>