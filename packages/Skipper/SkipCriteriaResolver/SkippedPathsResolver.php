<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipCriteriaResolver;

use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\FileSystem\FilePathHelper;
/**
 * @see \Rector\Tests\Skipper\SkipCriteriaResolver\SkippedPathsResolver\SkippedPathsResolverTest
 */
final class SkippedPathsResolver
{
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @var string[]
     */
    private $skippedPaths = [];
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
        if ($this->skippedPaths !== [] && !\defined('PHPUNIT_COMPOSER_INSTALL')) {
            return $this->skippedPaths;
        }
        $skip = SimpleParameterProvider::provideArrayParameter(Option::SKIP);
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
