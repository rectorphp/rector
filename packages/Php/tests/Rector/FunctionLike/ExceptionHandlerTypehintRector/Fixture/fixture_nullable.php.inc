<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehintNullable()
{
    function handler(?Exception $exception)
    {
    }

    set_error_handler('handler');
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehintNullable()
{
    function handler(?\Throwable $exception)
    {
    }

    set_error_handler('handler');
}

?>
