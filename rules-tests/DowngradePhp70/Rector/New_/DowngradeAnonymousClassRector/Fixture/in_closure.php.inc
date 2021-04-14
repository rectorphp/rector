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
    class AnonymousFor_NotInFunction
    {
        public function execute()
        {
        }
    }
    return new AnonymousFor_NotInFunction();
};

?>