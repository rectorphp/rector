<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\Fragment;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerReference;
use RectorPrefix20211020\Symfony\Component\HttpKernel\EventListener\FragmentListener;
/**
 * Adds the possibility to generate a fragment URI for a given Controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class RoutableFragmentRenderer implements \RectorPrefix20211020\Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface
{
    /**
     * @internal
     */
    protected $fragmentPath = '/_fragment';
    /**
     * Sets the fragment path that triggers the fragment listener.
     *
     * @see FragmentListener
     * @param string $path
     */
    public function setFragmentPath($path)
    {
        $this->fragmentPath = $path;
    }
    /**
     * Generates a fragment URI for a given controller.
     *
     * @param bool $absolute Whether to generate an absolute URL or not
     * @param bool $strict   Whether to allow non-scalar attributes or not
     *
     * @return string A fragment URI
     * @param \Symfony\Component\HttpKernel\Controller\ControllerReference $reference
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function generateFragmentUri($reference, $request, $absolute = \false, $strict = \true)
    {
        return (new \RectorPrefix20211020\Symfony\Component\HttpKernel\Fragment\FragmentUriGenerator($this->fragmentPath))->generate($reference, $request, $absolute, $strict, \false);
    }
}
