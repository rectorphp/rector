<?php

namespace Rector\Php70\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehintNullable()
{
    function handler(?\Exception $exception)
    {
    }

    set_error_handler('handler');
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehintNullable()
{
    function handler(?\Throwable $exception)
    {
    }

    set_error_handler('handler');
}

?>
