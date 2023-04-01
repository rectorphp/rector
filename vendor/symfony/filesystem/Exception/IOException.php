<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\Filesystem\Exception;

/**
 * Exception class thrown when a filesystem operation failure happens.
 *
 * @author Romain Neutron <imprec@gmail.com>
 * @author Christian Gärtner <christiangaertner.film@googlemail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IOException extends \RuntimeException implements IOExceptionInterface
{
    /**
     * @var string|null
     */
    private $path;
    public function __construct(string $message, int $code = 0, \Throwable $previous = null, string $path = null)
    {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }
    public function getPath() : ?string
    {
        return $this->path;
    }
}
