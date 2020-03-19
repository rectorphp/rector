<?php

declare(strict_types=1);

namespace Rector\Core\Exception;

use Exception;
use Rector\Core\Contract\Rector\RectorInterface;

final class NotRectorException extends Exception
{
    public function __construct(string $class)
    {
        $message = sprintf('"%s" should be type of "%s"', $class, RectorInterface::class);

        parent::__construct($message);
    }
}
