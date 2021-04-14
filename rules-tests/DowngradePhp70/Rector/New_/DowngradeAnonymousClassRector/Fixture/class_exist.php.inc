<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class AnonymousFor_ClassExists
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

class AnonymousFor_ClassExists
{
}
class AnonymousFor_ClassExists1
{
    public function execute()
    {
    }
}
class ClassExists
{
    public function run()
    {
        return new AnonymousFor_ClassExists1();
    }
}

?>