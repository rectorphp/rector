<?php

namespace RectorPrefix20210827\Symfony\Component\Mime;

if (\class_exists('Symfony\\Component\\Mime\\Address')) {
    return;
}
class Address
{
    /**
     * @param string $address
     * @param string $name
     */
    public function __construct($address, $name = '')
    {
    }
}
