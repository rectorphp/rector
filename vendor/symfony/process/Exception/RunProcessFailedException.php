<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202506\Symfony\Component\Process\Exception;

use RectorPrefix202506\Symfony\Component\Process\Messenger\RunProcessContext;
/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunProcessFailedException extends RuntimeException
{
    /**
     * @readonly
     */
    public RunProcessContext $context;
    public function __construct(ProcessFailedException $exception, RunProcessContext $context)
    {
        $this->context = $context;
        parent::__construct($exception->getMessage(), $exception->getCode());
    }
}
