<?php

function multiDirname3()
{
    dirname(\dirname(dirname($path)));

    dirname(dirname(foo(dirname(dirname($path)))));
}

?>
-----
<?php

function multiDirname3()
{
    dirname($path, 3);

    dirname(foo(dirname($path, 2)), 2);
}

?>
