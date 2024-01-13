<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipCriteriaResolver;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
final class SkippedClassResolver
{
    /**
     * @var null|array<string, string[]|null>
     */
    private $skippedClasses = null;
    /**
     * @return array<string, string[]|null>
     */
    public function resolve() : array
    {
        // disable cache in tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $this->skippedClasses = null;
        }
        // already cached, even only empty array
        if ($this->skippedClasses !== null) {
            return $this->skippedClasses;
        }
        $skip = SimpleParameterProvider::provideArrayParameter(Option::SKIP);
        $this->skippedClasses = [];
        foreach ($skip as $key => $value) {
            // e.g. [SomeClass::class] → shift values to [SomeClass::class => null]
            if (\is_int($key)) {
                $key = $value;
                $value = null;
            }
            if (!\is_string($key)) {
                continue;
            }
            // this only checks for Rector rules, that are always autoloaded
            if (!\class_exists($key) && !\interface_exists($key)) {
                continue;
            }
            $this->skippedClasses[$key] = $value;
        }
        return $this->skippedClasses;
    }
}
