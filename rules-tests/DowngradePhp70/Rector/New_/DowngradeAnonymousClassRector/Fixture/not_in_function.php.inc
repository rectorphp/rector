<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

return new class {
    public function execute()
    {
    }
};

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class AnonymousFor_NotInFunction
{
    public function execute()
    {
    }
}
return new AnonymousFor_NotInFunction();

?>