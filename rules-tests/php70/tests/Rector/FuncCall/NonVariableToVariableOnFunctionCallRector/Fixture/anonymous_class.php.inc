<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function anonymousClass()
{
    $anonymousClass = new class {
        public function bar(&$baz) {}
    };
    $anonymousClass->bar(baz());
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function anonymousClass()
{
    $anonymousClass = new class {
        public function bar(&$baz) {}
    };
    $baz = baz();
    $anonymousClass->bar($baz);
}

?>
