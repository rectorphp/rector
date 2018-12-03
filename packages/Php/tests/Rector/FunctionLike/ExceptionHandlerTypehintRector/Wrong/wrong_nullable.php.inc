<?php

function exceptionHandlerTypehintNullable()
{
    function handler(?Exception $exception)
    {
    }

    set_error_handler('handler');
}

?>
---
<?php

function exceptionHandlerTypehintNullable()
{
    function handler(?\Throwable $exception)
    {
    }

    set_error_handler('handler');
}

?>
