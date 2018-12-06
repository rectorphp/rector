<?php

function trailingCommaArguments()
{
    calling(
        // comment
        $one,
        $two
    );
}

?>
-----
<?php

function trailingCommaArguments()
{
    calling(
        // comment
        $one,
        $two,
    );
}

?>
