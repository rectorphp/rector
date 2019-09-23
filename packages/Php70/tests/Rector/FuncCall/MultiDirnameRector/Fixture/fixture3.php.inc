<?php

namespace Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector\Fixture;

function multiDirname3()
{
    dirname(\dirname(dirname($path)));

    dirname(dirname(foo(dirname(dirname($path)))));
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector\Fixture;

function multiDirname3()
{
    dirname($path, 3);

    dirname(foo(dirname($path, 2)), 2);
}

?>
