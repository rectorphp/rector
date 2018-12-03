<?php

function exceptionHandlerTypehint()
{
    function handler(Exception $exception)
    {

    }

    set_error_handler('handler');
}

?>
-----
<?php

function exceptionHandlerTypehint()
{
    function handler(\Throwable $exception)
    {

    }

    set_error_handler('handler');
}

?>
