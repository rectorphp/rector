<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipVoter;

use PHPStan\Reflection\ReflectionProvider;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Rector\Skipper\Skipper\SkipSkipper;
final class ClassSkipVoter
{
    /**
     * @readonly
     * @var \Rector\Skipper\Skipper\SkipSkipper
     */
    private $skipSkipper;
    /**
     * @readonly
     * @var \Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver
     */
    private $skippedClassResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(SkipSkipper $skipSkipper, SkippedClassResolver $skippedClassResolver, ReflectionProvider $reflectionProvider)
    {
        $this->skipSkipper = $skipSkipper;
        $this->skippedClassResolver = $skippedClassResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        if (\is_object($element)) {
            return \true;
        }
        return $this->reflectionProvider->hasClass($element);
    }
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, string $filePath) : bool
    {
        $skippedClasses = $this->skippedClassResolver->resolve();
        return $this->skipSkipper->doesMatchSkip($element, $filePath, $skippedClasses);
    }
}
