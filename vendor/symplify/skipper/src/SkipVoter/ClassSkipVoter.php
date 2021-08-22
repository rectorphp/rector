<?php

declare (strict_types=1);
namespace RectorPrefix20210822\Symplify\Skipper\SkipVoter;

use RectorPrefix20210822\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210822\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use RectorPrefix20210822\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix20210822\Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use RectorPrefix20210822\Symplify\Skipper\Skipper\OnlySkipper;
use RectorPrefix20210822\Symplify\Skipper\Skipper\SkipSkipper;
use RectorPrefix20210822\Symplify\Skipper\ValueObject\Option;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ClassSkipVoter implements \RectorPrefix20210822\Symplify\Skipper\Contract\SkipVoterInterface
{
    /**
     * @var \RectorPrefix20210822\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker
     */
    private $classLikeExistenceChecker;
    /**
     * @var \RectorPrefix20210822\Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \RectorPrefix20210822\Symplify\Skipper\Skipper\SkipSkipper
     */
    private $skipSkipper;
    /**
     * @var \RectorPrefix20210822\Symplify\Skipper\Skipper\OnlySkipper
     */
    private $onlySkipper;
    /**
     * @var \RectorPrefix20210822\Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver
     */
    private $skippedClassResolver;
    public function __construct(\RectorPrefix20210822\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker $classLikeExistenceChecker, \RectorPrefix20210822\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20210822\Symplify\Skipper\Skipper\SkipSkipper $skipSkipper, \RectorPrefix20210822\Symplify\Skipper\Skipper\OnlySkipper $onlySkipper, \RectorPrefix20210822\Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver $skippedClassResolver)
    {
        $this->classLikeExistenceChecker = $classLikeExistenceChecker;
        $this->parameterProvider = $parameterProvider;
        $this->skipSkipper = $skipSkipper;
        $this->onlySkipper = $onlySkipper;
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
     * @param \RectorPrefix20210822\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo
     */
    public function shouldSkip($element, $smartFileInfo) : bool
    {
        $only = $this->parameterProvider->provideArrayParameter(\RectorPrefix20210822\Symplify\Skipper\ValueObject\Option::ONLY);
        $doesMatchOnly = $this->onlySkipper->doesMatchOnly($element, $smartFileInfo, $only);
        if (\is_bool($doesMatchOnly)) {
            return $doesMatchOnly;
        }
        $skippedClasses = $this->skippedClassResolver->resolve();
        return $this->skipSkipper->doesMatchSkip($element, $smartFileInfo, $skippedClasses);
    }
}
