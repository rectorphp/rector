<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202410\Symfony\Component\Console\Exception;

use RectorPrefix202410\Symfony\Component\Console\Messenger\RunCommandContext;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunCommandFailedException extends RuntimeException
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Messenger\RunCommandContext
     */
    public $context;
    /**
     * @param \Throwable|string $exception
     */
    public function __construct($exception, RunCommandContext $context)
    {
        $this->context = $context;
        parent::__construct($exception instanceof \Throwable ? $exception->getMessage() : $exception, $exception instanceof \Throwable ? $exception->getCode() : 0, $exception instanceof \Throwable ? $exception : null);
    }
}
