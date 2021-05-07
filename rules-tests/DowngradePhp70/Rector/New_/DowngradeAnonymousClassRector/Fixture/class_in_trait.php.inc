<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

trait ClassInTrait
{
    public function run()
    {
        $message = 'error';
        return new class($message) extends \InvalidArgumentException {};
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class AnonymousFor_ClassInTrait extends \InvalidArgumentException
{
}
trait ClassInTrait
{
    public function run()
    {
        $message = 'error';
        return new AnonymousFor_ClassInTrait($message);
    }
}

?>
