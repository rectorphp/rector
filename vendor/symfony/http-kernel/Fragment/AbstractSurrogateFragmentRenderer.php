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
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Response;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerReference;
use RectorPrefix20211020\Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\UriSigner;
/**
 * Implements Surrogate rendering strategy.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractSurrogateFragmentRenderer extends \RectorPrefix20211020\Symfony\Component\HttpKernel\Fragment\RoutableFragmentRenderer
{
    private $surrogate;
    private $inlineStrategy;
    private $signer;
    /**
     * The "fallback" strategy when surrogate is not available should always be an
     * instance of InlineFragmentRenderer.
     *
     * @param FragmentRendererInterface $inlineStrategy The inline strategy to use when the surrogate is not supported
     */
    public function __construct(\RectorPrefix20211020\Symfony\Component\HttpKernel\HttpCache\SurrogateInterface $surrogate = null, \RectorPrefix20211020\Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface $inlineStrategy, \RectorPrefix20211020\Symfony\Component\HttpKernel\UriSigner $signer = null)
    {
        $this->surrogate = $surrogate;
        $this->inlineStrategy = $inlineStrategy;
        $this->signer = $signer;
    }
    /**
     * {@inheritdoc}
     *
     * Note that if the current Request has no surrogate capability, this method
     * falls back to use the inline rendering strategy.
     *
     * Additional available options:
     *
     *  * alt: an alternative URI to render in case of an error
     *  * comment: a comment to add when returning the surrogate tag
     *
     * Note, that not all surrogate strategies support all options. For now
     * 'alt' and 'comment' are only supported by ESI.
     *
     * @see Symfony\Component\HttpKernel\HttpCache\SurrogateInterface
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed[] $options
     */
    public function render($uri, $request, $options = [])
    {
        if (!$this->surrogate || !$this->surrogate->hasSurrogateCapability($request)) {
            if ($uri instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerReference && $this->containsNonScalars($uri->attributes)) {
                throw new \InvalidArgumentException('Passing non-scalar values as part of URI attributes to the ESI and SSI rendering strategies is not supported. Use a different rendering strategy or pass scalar values.');
            }
            return $this->inlineStrategy->render($uri, $request, $options);
        }
        if ($uri instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerReference) {
            $uri = $this->generateSignedFragmentUri($uri, $request);
        }
        $alt = $options['alt'] ?? null;
        if ($alt instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerReference) {
            $alt = $this->generateSignedFragmentUri($alt, $request);
        }
        $tag = $this->surrogate->renderIncludeTag($uri, $alt, $options['ignore_errors'] ?? \false, $options['comment'] ?? '');
        return new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Response($tag);
    }
    private function generateSignedFragmentUri(\RectorPrefix20211020\Symfony\Component\HttpKernel\Controller\ControllerReference $uri, \RectorPrefix20211020\Symfony\Component\HttpFoundation\Request $request) : string
    {
        return (new \RectorPrefix20211020\Symfony\Component\HttpKernel\Fragment\FragmentUriGenerator($this->fragmentPath, $this->signer))->generate($uri, $request);
    }
    private function containsNonScalars(array $values) : bool
    {
        foreach ($values as $value) {
            if (\is_array($value)) {
                return $this->containsNonScalars($value);
            } elseif (!\is_scalar($value) && null !== $value) {
                return \true;
            }
        }
        return \false;
    }
}
