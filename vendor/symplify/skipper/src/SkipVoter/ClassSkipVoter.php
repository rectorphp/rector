<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Skipper\SkipVoter;

use RectorPrefix20210510\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210510\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use RectorPrefix20210510\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix20210510\Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use RectorPrefix20210510\Symplify\Skipper\Skipper\OnlySkipper;
use RectorPrefix20210510\Symplify\Skipper\Skipper\SkipSkipper;
use RectorPrefix20210510\Symplify\Skipper\ValueObject\Option;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ClassSkipVoter implements SkipVoterInterface
{
    /**
     * @var ClassLikeExistenceChecker
     */
    private $classLikeExistenceChecker;
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var SkipSkipper
     */
    private $skipSkipper;
    /**
     * @var OnlySkipper
     */
    private $onlySkipper;
    /**
     * @var SkippedClassResolver
     */
    private $skippedClassResolver;
    public function __construct(ClassLikeExistenceChecker $classLikeExistenceChecker, ParameterProvider $parameterProvider, SkipSkipper $skipSkipper, OnlySkipper $onlySkipper, SkippedClassResolver $skippedClassResolver)
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
     */
    public function shouldSkip($element, SmartFileInfo $smartFileInfo) : bool
    {
        $only = $this->parameterProvider->provideArrayParameter(Option::ONLY);
        $doesMatchOnly = $this->onlySkipper->doesMatchOnly($element, $smartFileInfo, $only);
        if (\is_bool($doesMatchOnly)) {
            return $doesMatchOnly;
        }
        $skippedClasses = $this->skippedClassResolver->resolve();
        return $this->skipSkipper->doesMatchSkip($element, $smartFileInfo, $skippedClasses);
    }
}
