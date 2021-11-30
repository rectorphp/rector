<?php

namespace Foo {

    function strlen($s) {
        return \strlen($s) + 1000;
    }

}

namespace {

    use function Foo\strlen;

    echo strlen('test'), '/', \strlen('test');

}
?>
-----
<?php

namespace Foo {

    function strlen($s) {
        return \strlen($s) + 1000;
    }

}

namespace {

    echo \Foo\strlen('test'), '/', \strlen('test');

}
?>
