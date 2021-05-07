<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

function () {
    return new class {
            public function execute()
            {
            }
    };
};

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector\Fixture;

function () {
    return new Anonymous__%s__0();
};
class Anonymous__%s__0
{
    public function execute()
    {
    }
}
