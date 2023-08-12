<?php

declare (strict_types=1);
namespace Rector\Skipper\SkipCriteriaResolver;

use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
final class SkippedClassResolver
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var array<string, string[]|null>
     */
    private $skippedClasses = [];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<string, string[]|null>
     */
    public function resolve() : array
    {
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            // disable cache in tests
            $this->skippedClasses = [];
        }
        // skip cache in tests
        if ($this->skippedClasses !== []) {
            return $this->skippedClasses;
        }
        $skip = SimpleParameterProvider::provideArrayParameter(Option::SKIP);
        foreach ($skip as $key => $value) {
            // e.g. [SomeClass::class] → shift values to [SomeClass::class => null]
            if (\is_int($key)) {
                $key = $value;
                $value = null;
            }
            if (!\is_string($key)) {
                continue;
            }
            if (!$this->reflectionProvider->hasClass($key)) {
                continue;
            }
            $this->skippedClasses[$key] = $value;
        }
        return $this->skippedClasses;
    }
}
