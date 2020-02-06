<?php

namespace Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\Klarka;

class KlarkaExtended extends Klarka
{
    private $existingProperty;

    public function run()
    {
        if ($this->leafBreadcrumbCategory) {
            $category = $this->leafBreadcrumbCategory;
        }

        while ($this->leafLet !== 5) {
        }

        while ($this->existingProperty !== 5) {
        }
        while ($this->existingProperty) {
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source\Klarka;

class KlarkaExtended extends Klarka
{
    private $existingProperty;

    public function run()
    {
        if ($this->get('leafBreadcrumbCategory')) {
            $category = $this->get('leafBreadcrumbCategory');
        }

        while ($this->get('leafLet') !== 5) {
        }

        while ($this->existingProperty !== 5) {
        }
        while ($this->existingProperty) {
        }
    }
}

?>
