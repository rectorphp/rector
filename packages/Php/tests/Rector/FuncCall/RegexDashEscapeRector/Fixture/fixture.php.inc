<?php

function regexDashEscapeRector()
{
    // keep
    preg_match("#[\s()-]#", 'some text');
    preg_match("#[-\W()]#", 'some text');

    // change
    preg_match("#[\w-()]#", 'some text');
    preg_match("#[\W-()]#", 'some text');
    preg_match('#[\w-()]#', 'some text');
}

?>
-----
<?php

function regexDashEscapeRector()
{
    // keep
    preg_match("#[\s()-]#", 'some text');
    preg_match("#[-\W()]#", 'some text');

    // change
    preg_match("#[\w\-()]#", 'some text');
    preg_match("#[\W\-()]#", 'some text');
    preg_match('#[\w\-()]#', 'some text');
}

?>
