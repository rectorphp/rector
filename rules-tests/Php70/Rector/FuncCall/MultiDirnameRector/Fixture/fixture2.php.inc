<?php

namespace Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector\Fixture;

function multiDirname2()
{
    dirname(dirname($path), 3);

    dirname(dirname($path, 2), 3);

    dirname(dirname(dirname($path), 3));
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector\Fixture;

function multiDirname2()
{
    dirname($path, 4);

    dirname($path, 5);

    dirname($path, 5);
}

?>
