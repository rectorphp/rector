<?php

namespace Rector\Nette\Tests\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector\Fixture;

use Nette\Utils\Html;

final class Fixture
{
    public function run()
    {
        $html = Html::el();
        $html->setClass('first');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector\Fixture;

use Nette\Utils\Html;

final class Fixture
{
    public function run()
    {
        $html = Html::el();
        $html->appendAttribute('class', 'first');
    }
}

?>
