<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\Event;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Response;
use RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface;
/**
 * Allows to execute logic after a response was sent.
 *
 * Since it's only triggered on main requests, the `getRequestType()` method
 * will always return the value of `HttpKernelInterface::MAIN_REQUEST`.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
final class TerminateEvent extends \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\KernelEvent
{
    private $response;
    public function __construct(\RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface $kernel, \RectorPrefix20211020\Symfony\Component\HttpFoundation\Request $request, \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response $response)
    {
        parent::__construct($kernel, $request, \RectorPrefix20211020\Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST);
        $this->response = $response;
    }
    public function getResponse() : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response
    {
        return $this->response;
    }
}
