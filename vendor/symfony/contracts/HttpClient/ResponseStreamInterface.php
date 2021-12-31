<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Symfony\Contracts\HttpClient;

/**
 * Yields response chunks, returned by HttpClientInterface::stream().
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @extends \Iterator<ResponseInterface, ChunkInterface>
 */
interface ResponseStreamInterface extends \Iterator
{
    public function key() : \RectorPrefix20211231\Symfony\Contracts\HttpClient\ResponseInterface;
    public function current() : \RectorPrefix20211231\Symfony\Contracts\HttpClient\ChunkInterface;
}
