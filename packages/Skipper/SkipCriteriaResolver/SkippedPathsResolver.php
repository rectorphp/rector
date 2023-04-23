<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipCriteriaResolver;

use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\FileSystem\FilePathHelper;
/**
 * @see \Rector\Tests\Skipper\SkipCriteriaResolver\SkippedPathsResolver\SkippedPathsResolverTest
 */
final class SkippedPathsResolver
{
    /**
     * @var string[]
     */
    private $skippedPaths = [];
    /**
     * @readonly
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    public function __construct(ParameterProvider $parameterProvider, FilePathHelper $filePathHelper)
    {
        $this->parameterProvider = $parameterProvider;
        $this->filePathHelper = $filePathHelper;
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
            if (\strpos((string) $value, '*') !== \false) {
                $this->skippedPaths[] = $this->filePathHelper->normalizePathAndSchema($value);
                continue;
            }
            if (\file_exists($value)) {
                $this->skippedPaths[] = $this->filePathHelper->normalizePathAndSchema($value);
            }
        }
        return $this->skippedPaths;
    }
}
