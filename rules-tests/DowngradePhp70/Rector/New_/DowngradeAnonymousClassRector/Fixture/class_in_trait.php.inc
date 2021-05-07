<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

trait ClassInTrait
{
    public function run()
    {
        $message = 'error';

        $first = new class($message) extends \InvalidArgumentException {};

        $second = new class() extends \Exception {};
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

trait ClassInTrait
{
    public function run()
    {
        $message = 'error';

        $first = new Anonymous__%s__0($message);

        $second = new Anonymous__%s__1();
    }
}
class Anonymous__%s__0 extends \InvalidArgumentException
{
}
class Anonymous__%s__1 extends \Exception
{
}
