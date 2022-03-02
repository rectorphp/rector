<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220302\Symfony\Contracts\HttpClient\Exception;

use RectorPrefix20220302\Symfony\Contracts\HttpClient\ResponseInterface;
/**
 * Base interface for HTTP-related exceptions.
 *
 * @author Anton Chernikov <anton_ch1989@mail.ru>
 */
interface HttpExceptionInterface extends \RectorPrefix20220302\Symfony\Contracts\HttpClient\Exception\ExceptionInterface
{
    public function getResponse() : \RectorPrefix20220302\Symfony\Contracts\HttpClient\ResponseInterface;
}
