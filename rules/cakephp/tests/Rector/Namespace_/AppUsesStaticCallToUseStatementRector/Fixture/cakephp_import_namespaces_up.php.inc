<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

class ImportNamespacesUp
{
    public function test()
    {
        \App::uses('HtmlDomLib', 'Foo.Lib');
        $HtmlDom = new HtmlDomLib();
        \App::uses('HtmlDomLibExt', 'Foo.Lib');
        $HtmlDom = new HtmlDomLibExt();
    }
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

use Foo\Lib\HtmlDomLib;
use Foo\Lib\HtmlDomLibExt;
class ImportNamespacesUp
{
    public function test()
    {
        $HtmlDom = new HtmlDomLib();
        $HtmlDom = new HtmlDomLibExt();
    }
}

?>
