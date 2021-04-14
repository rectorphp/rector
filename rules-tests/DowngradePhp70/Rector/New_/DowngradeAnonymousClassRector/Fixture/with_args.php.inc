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

class AnonymousFor_WithArgs
{
    public function execute()
    {
    }
}
class WithArgs
{
    public function run($arg1, $arg2)
    {
        return new AnonymousFor_WithArgs($arg1, $arg2);
    }
}

?>