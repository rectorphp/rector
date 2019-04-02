<?php

namespace Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Fixture;

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\Klarka;

class KlarkaExtended extends Klarka
{
    public function run()
    {
        if ($this->leafBreadcrumbCategory) {
            $category = $this->leafBreadcrumbCategory;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Fixture;

use Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\Klarka;

class KlarkaExtended extends Klarka
{
    public function run()
    {
        if ($this->get('leafBreadcrumbCategory')) {
            $category = $this->get('leafBreadcrumbCategory');
        }
    }
}

?>
