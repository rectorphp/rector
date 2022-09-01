<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipVoter;

use Rector\Skipper\Contract\SkipVoterInterface;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Rector\Skipper\Skipper\SkipSkipper;
use RectorPrefix202209\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
final class ClassSkipVoter implements SkipVoterInterface
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker
     */
    private $classLikeExistenceChecker;
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
    public function __construct(ClassLikeExistenceChecker $classLikeExistenceChecker, SkipSkipper $skipSkipper, SkippedClassResolver $skippedClassResolver)
    {
        $this->classLikeExistenceChecker = $classLikeExistenceChecker;
        $this->skipSkipper = $skipSkipper;
        $this->skippedClassResolver = $skippedClassResolver;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        if (\is_object($element)) {
            return \true;
        }
        return $this->classLikeExistenceChecker->doesClassLikeExist($element);
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
