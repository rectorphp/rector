<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class Anonymous
{
}
class ClassExists
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

class Anonymous
{
}
class Anonymous1
{
}
class ClassExists
{
    public function run()
    {
        return new Anonymous1();
    }
}

?>