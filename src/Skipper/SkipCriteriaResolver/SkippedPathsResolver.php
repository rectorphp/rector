<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipCriteriaResolver;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\FileSystem\FilePathHelper;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
/**
 * @see \Rector\Tests\Skipper\SkipCriteriaResolver\SkippedPathsResolver\SkippedPathsResolverTest
 */
final class SkippedPathsResolver
{
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @var null|string[]
     */
    private $skippedPaths = null;
    public function __construct(FilePathHelper $filePathHelper)
    {
        $this->filePathHelper = $filePathHelper;
    }
    /**
     * @return string[]
     */
    public function resolve() : array
    {
        // disable cache in tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $this->skippedPaths = null;
        }
        // already cached, even only empty array
        if ($this->skippedPaths !== null) {
            return $this->skippedPaths;
        }
        $skip = SimpleParameterProvider::provideArrayParameter(Option::SKIP);
        $this->skippedPaths = [];
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
