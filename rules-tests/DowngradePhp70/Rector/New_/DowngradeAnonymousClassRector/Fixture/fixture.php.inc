<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class SomeClass
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

class SomeClass
{
    public function run()
    {
        return new Anonymous();
    }
}
class Anonymous
{
    public function execute()
    {
    }
}

?>