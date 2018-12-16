<?php

function regexDashEscapeRector()
{
    preg_match("#[\w()-]#", 'some text'); // ok
    preg_match("#[-\w()]#", 'some text'); // ok
    preg_match("#[\w-()]#", 'some text'); // NOPE!
    preg_match("#[\w(-)]#", 'some text'); // ok
}

?>
-----
<?php

function regexDashEscapeRector()
{
    preg_match("#[\w()-]#", 'some text'); // ok
    preg_match("#[-\w()]#", 'some text'); // ok
    preg_match("#[\w\-()]#", 'some text'); // NOPE!
    preg_match("#[\w(-)]#", 'some text'); // ok
}

?>
