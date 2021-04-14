<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class Fixture
{
    public function run()
    {
        return new class {
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

class AnonymousFor_Fixture
{
    public function execute()
    {
    }
}
class Fixture
{
    public function run()
    {
        return new AnonymousFor_Fixture();
    }
}

?>