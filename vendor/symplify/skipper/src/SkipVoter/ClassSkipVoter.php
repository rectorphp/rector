<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\Skipper\SkipVoter;

use RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220501\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
use RectorPrefix20220501\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix20220501\Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use RectorPrefix20220501\Symplify\Skipper\Skipper\OnlySkipper;
use RectorPrefix20220501\Symplify\Skipper\Skipper\SkipSkipper;
use RectorPrefix20220501\Symplify\Skipper\ValueObject\Option;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ClassSkipVoter implements \RectorPrefix20220501\Symplify\Skipper\Contract\SkipVoterInterface
{
    /**
     * @var \Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker
     */
    private $classLikeExistenceChecker;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Symplify\Skipper\Skipper\SkipSkipper
     */
    private $skipSkipper;
    /**
     * @var \Symplify\Skipper\Skipper\OnlySkipper
     */
    private $onlySkipper;
    /**
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver
     */
    private $skippedClassResolver;
    public function __construct(\RectorPrefix20220501\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker $classLikeExistenceChecker, \RectorPrefix20220501\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20220501\Symplify\Skipper\Skipper\SkipSkipper $skipSkipper, \RectorPrefix20220501\Symplify\Skipper\Skipper\OnlySkipper $onlySkipper, \RectorPrefix20220501\Symplify\Skipper\SkipCriteriaResolver\SkippedClassResolver $skippedClassResolver)
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
    public function shouldSkip($element, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool
    {
        $only = $this->parameterProvider->provideArrayParameter(\RectorPrefix20220501\Symplify\Skipper\ValueObject\Option::ONLY);
        $doesMatchOnly = $this->onlySkipper->doesMatchOnly($element, $smartFileInfo, $only);
        if (\is_bool($doesMatchOnly)) {
            return $doesMatchOnly;
        }
        $skippedClasses = $this->skippedClassResolver->resolve();
        return $this->skipSkipper->doesMatchSkip($element, $smartFileInfo, $skippedClasses);
    }
}
