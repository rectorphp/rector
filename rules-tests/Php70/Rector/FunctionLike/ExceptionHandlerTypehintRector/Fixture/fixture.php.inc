<?php

namespace Rector\Tests\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehint()
{
    function handler(\Exception $exception)
    {

    }

    set_error_handler('handler');
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehint()
{
    function handler(\Throwable $exception)
    {

    }

    set_error_handler('handler');
}

?>
