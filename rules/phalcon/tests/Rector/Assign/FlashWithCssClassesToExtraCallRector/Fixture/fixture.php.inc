<?php

namespace Rector\Phalcon\Tests\Rector\Assign\FlashWithCssClassesToExtraCallRector\Fixture;

class Fixture
{
    public function run()
    {
        $cssClasses = [];
        $flash = new \Phalcon\Flash($cssClasses);
    }
}

?>
-----
<?php

namespace Rector\Phalcon\Tests\Rector\Assign\FlashWithCssClassesToExtraCallRector\Fixture;

class Fixture
{
    public function run()
    {
        $cssClasses = [];
        $flash = new \Phalcon\Flash();
        $flash->setCssClasses($cssClasses);
    }
}

?>
