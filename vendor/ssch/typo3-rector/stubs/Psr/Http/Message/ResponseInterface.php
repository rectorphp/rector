<?php

namespace RectorPrefix20211020\Psr\Http\Message;

if (\interface_exists('Psr\\Http\\Message\\ResponseInterface')) {
    return;
}
interface ResponseInterface
{
    /**
     * @param string $code
     * @param string $reasonPhrase
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withStatus($code, $reasonPhrase = '');
}
