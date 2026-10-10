<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use PhpParser\Node;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\RectorInterface;
use Rector\ProcessAnalyzer\RectifiedAnalyzer;
use Rector\Skipper\Matcher\FileInfoMatcher;
use Rector\Skipper\ValueObject\SkipMatch;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
/**
 * @api
 * @see \Rector\Tests\Skipper\Skipper\SkipperTest
 */
final class Skipper
{
    /**
     * @readonly
     */
    private RectifiedAnalyzer $rectifiedAnalyzer;
    /**
     * @readonly
     */
    private \Rector\Skipper\Skipper\PathSkipper $pathSkipper;
    /**
     * @readonly
     */
    private FileInfoMatcher $fileInfoMatcher;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var null|array<class-string, string[]|null>
     */
    private $skippedClassesToFiles = null;
    /**
     * Map of skip element (rule class or global path) to the set of paths matched under it.
     * Rule-scoped skips collect their matched paths; skip-everywhere rules and global path skips
     * keep an empty path set, the same shape as the "->withSkip()" config.
     *
     * @var array<string, array<string, true>>
     */
    private array $usedSkips = [];
    public function __construct(RectifiedAnalyzer $rectifiedAnalyzer, \Rector\Skipper\Skipper\PathSkipper $pathSkipper, FileInfoMatcher $fileInfoMatcher, ReflectionProvider $reflectionProvider)
    {
        $this->rectifiedAnalyzer = $rectifiedAnalyzer;
        $this->pathSkipper = $pathSkipper;
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function shouldSkipFilePath(string $filePath): bool
    {
        $matchedPath = $this->pathSkipper->matchSkippedPath($filePath);
        if ($matchedPath === null) {
            return \false;
        }
        $this->markUsed($matchedPath);
        return \true;
    }
    public function shouldSkipRectorAndFile(object $rector, string $filePath): bool
    {
        $skipMatch = $this->matchSkip($rector, $filePath);
        if (!$skipMatch instanceof SkipMatch) {
            return \false;
        }
        $this->markSkipUsed($skipMatch);
        return \true;
    }
    /**
     * Match a class/path skip without marking it used. Callers that can only tell whether the skip
     * actually prevented a change later on must mark it used themselves via markSkipUsed().
     * @param string|object $element
     */
    public function matchSkip($element, string $filePath): ?SkipMatch
    {
        if (!is_object($element) && !$this->reflectionProvider->hasClass($element)) {
            return null;
        }
        foreach ($this->resolveSkippedClasses() as $skippedClass => $skippedFiles) {
            if (!is_a($element, $skippedClass, \true)) {
                continue;
            }
            // skip everywhere
            if (!is_array($skippedFiles)) {
                return new SkipMatch($skippedClass, null);
            }
            // the same path can be skipped under multiple rules, so the matched path is reported
            // scoped to its rule, not tracked on its own
            $matchedPath = $this->fileInfoMatcher->matchPattern($filePath, $skippedFiles);
            if ($matchedPath !== null) {
                return new SkipMatch($skippedClass, $matchedPath);
            }
        }
        return null;
    }
    public function markSkipUsed(SkipMatch $skipMatch): void
    {
        $this->markUsed($skipMatch->getSkippedClass(), $skipMatch->getMatchedPath());
    }
    /**
     * Skip elements (rule classes and paths) that actually matched during the run,
     * so unused skips can be reported and removed.
     *
     * @return array<string, string[]>
     */
    public function provideUsedSkips(): array
    {
        $usedSkips = [];
        foreach ($this->usedSkips as $skip => $paths) {
            $usedSkips[$skip] = array_keys($paths);
        }
        return $usedSkips;
    }
    /**
     * @return array<class-string, string[]|null>
     */
    public function resolveSkippedClasses(): array
    {
        // disable cache in tests
        if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $this->skippedClassesToFiles = null;
        }
        // already cached, even only empty array
        if ($this->skippedClassesToFiles !== null) {
            return $this->skippedClassesToFiles;
        }
        $skip = SimpleParameterProvider::provideArrayParameter(Option::SKIP);
        $this->skippedClassesToFiles = [];
        foreach ($skip as $key => $value) {
            // e.g. [SomeClass::class] → shift values to [SomeClass::class => null]
            if (is_int($key)) {
                $key = $value;
                $value = null;
            }
            if (!is_string($key)) {
                continue;
            }
            // this only checks for Rector rules, that are always autoloaded
            if (!class_exists($key) && !interface_exists($key)) {
                continue;
            }
            $this->skippedClassesToFiles[$key] = $value;
        }
        return $this->skippedClassesToFiles;
    }
    /**
     * @return array<class-string<DeprecatedInterface>>
     */
    public function resolveDeprecatedSkippedClasses(): array
    {
        $skippedClassNames = array_keys($this->resolveSkippedClasses());
        return array_filter($skippedClassNames, fn(string $class): bool => is_a($class, DeprecatedInterface::class, \true));
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function shouldSkipCurrentNode(string $rectorClass, Node $node): bool
    {
        return $this->rectifiedAnalyzer->hasRectified($rectorClass, $node);
    }
    private function markUsed(string $skip, ?string $path = null): void
    {
        $this->usedSkips[$skip] ??= [];
        if ($path !== null) {
            $this->usedSkips[$skip][$path] = \true;
        }
    }
}
