<?php declare(strict_types=1);

function handler(?\Throwable $exception)
{

}

set_error_handler('handler');
