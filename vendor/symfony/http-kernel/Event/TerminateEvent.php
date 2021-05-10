<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210510\Symfony\Component\HttpKernel\Event;

use RectorPrefix20210510\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20210510\Symfony\Component\HttpFoundation\Response;
use RectorPrefix20210510\Symfony\Component\HttpKernel\HttpKernelInterface;
/**
 * Allows to execute logic after a response was sent.
 *
 * Since it's only triggered on master requests, the `getRequestType()` method
 * will always return the value of `HttpKernelInterface::MASTER_REQUEST`.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
final class TerminateEvent extends KernelEvent
{
    private $response;
    public function __construct(HttpKernelInterface $kernel, Request $request, Response $response)
    {
        parent::__construct($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $this->response = $response;
    }
    public function getResponse() : Response
    {
        return $this->response;
    }
}
