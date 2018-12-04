<?php

function multiDirname()
{
    dirname(dirname($path));

    new dirname(dirname(dirname($path)));


// untouched
    dirname(dirname($path, $level));

    dirname("foo/" . dirname($path));

    dirname(dirname($path) . $foo);

    foo\dirname(dirname($path));
}

?>
-----
<?php

function multiDirname()
{
    dirname($path, 2);

    new dirname(dirname($path, 2));


// untouched
    dirname(dirname($path, $level));

    dirname("foo/" . dirname($path));

    dirname(dirname($path) . $foo);

    foo\dirname(dirname($path));
}

?>
