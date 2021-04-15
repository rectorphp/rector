<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

function inFunction()
{
    return new class {
            public function execute()
            {
            }
    };
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

class AnonymousFor_inFunction
{
    public function execute()
    {
    }
}
function inFunction()
{
    return new AnonymousFor_inFunction();
}

?>