<?php

declare(strict_types=1);

namespace Rector\Core\ValueObjectFactory;

use Rector\Core\ValueObject\ProcessResult;

final class ProcessResultFactory
{
    public function create(): ProcessResult
    {
        dump('___');
        die;
    }
}
