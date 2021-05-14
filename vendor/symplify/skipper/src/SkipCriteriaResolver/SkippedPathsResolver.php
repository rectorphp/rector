<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\Skipper\SkipCriteriaResolver;

use RectorPrefix20210514\Nette\Utils\Strings;
use RectorPrefix20210514\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20210514\Symplify\Skipper\ValueObject\Option;
use RectorPrefix20210514\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
/**
 * @see \Symplify\Skipper\Tests\SkipCriteriaResolver\SkippedPathsResolver\SkippedPathsResolverTest
 */
final class SkippedPathsResolver
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var string[]
     */
    private $skippedPaths = [];
    /**
     * @var PathNormalizer
     */
    private $pathNormalizer;
    public function __construct(\RectorPrefix20210514\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \RectorPrefix20210514\Symplify\SmartFileSystem\Normalizer\PathNormalizer $pathNormalizer)
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
        $skip = $this->parameterProvider->provideArrayParameter(\RectorPrefix20210514\Symplify\Skipper\ValueObject\Option::SKIP);
        foreach ($skip as $key => $value) {
            if (!\is_int($key)) {
                continue;
            }
            if (\file_exists($value)) {
                $this->skippedPaths[] = $this->pathNormalizer->normalizePath($value);
                continue;
            }
            if (\RectorPrefix20210514\Nette\Utils\Strings::contains($value, '*')) {
                $this->skippedPaths[] = $this->pathNormalizer->normalizePath($value);
                continue;
            }
        }
        return $this->skippedPaths;
    }
}
