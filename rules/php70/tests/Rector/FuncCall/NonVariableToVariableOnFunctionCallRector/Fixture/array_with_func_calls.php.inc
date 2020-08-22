<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function wrap($str) {}

function baz() {
    return [
        reset(wrap('foo')),
        reset(wrap('bar')),
        reset(wrap('baz')),
    ];
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector\Fixture;

function wrap($str) {}

function baz() {
    $wrap = wrap('foo');
    $wrap2 = wrap('bar');
    $wrap3 = wrap('baz');
    return [
        reset($wrap),
        reset($wrap2),
        reset($wrap3),
    ];
}

?>
