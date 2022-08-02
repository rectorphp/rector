<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Skipper\SkipCriteriaResolver;

use RectorPrefix202208\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202208\Symplify\Skipper\ValueObject\Option;
use RectorPrefix202208\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
/**
 * @see \Symplify\Skipper\Tests\SkipCriteriaResolver\SkippedPathsResolver\SkippedPathsResolverTest
 */
final class SkippedPathsResolver
{
    /**
     * @var string[]
     */
    private $skippedPaths = [];
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Symplify\SmartFileSystem\Normalizer\PathNormalizer
     */
    private $pathNormalizer;
    public function __construct(ParameterProvider $parameterProvider, PathNormalizer $pathNormalizer)
    {
        $this->parameterProvider = $parameterProvider;
        $this->pathNormalizer = $pathNormalizer;
    }
    /**
     * @return string[]
     */
    public function resolve() : array
    {
        if ($this->skippedPaths !== []) {
            return $this->skippedPaths;
        }
        $skip = $this->parameterProvider->provideArrayParameter(Option::SKIP);
        foreach ($skip as $key => $value) {
            if (!\is_int($key)) {
                continue;
            }
            if (\file_exists($value)) {
                $this->skippedPaths[] = $this->pathNormalizer->normalizePath($value);
                continue;
            }
            if (\strpos($value, '*') !== \false) {
                $this->skippedPaths[] = $this->pathNormalizer->normalizePath($value);
                continue;
            }
        }
        return $this->skippedPaths;
    }
}
