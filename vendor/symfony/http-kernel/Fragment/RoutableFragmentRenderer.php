<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210630\Symfony\Component\HttpKernel\Fragment;

use RectorPrefix20210630\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20210630\Symfony\Component\HttpKernel\Controller\ControllerReference;
use RectorPrefix20210630\Symfony\Component\HttpKernel\EventListener\FragmentListener;
/**
 * Adds the possibility to generate a fragment URI for a given Controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class RoutableFragmentRenderer implements \RectorPrefix20210630\Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface
{
    /**
     * @internal
     */
    protected $fragmentPath = '/_fragment';
    /**
     * Sets the fragment path that triggers the fragment listener.
     *
     * @see FragmentListener
     */
    public function setFragmentPath(string $path)
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
     */
    protected function generateFragmentUri(\RectorPrefix20210630\Symfony\Component\HttpKernel\Controller\ControllerReference $reference, \RectorPrefix20210630\Symfony\Component\HttpFoundation\Request $request, bool $absolute = \false, bool $strict = \true)
    {
        return (new \RectorPrefix20210630\Symfony\Component\HttpKernel\Fragment\FragmentUriGenerator($this->fragmentPath))->generate($reference, $request, $absolute, $strict, \false);
    }
}
