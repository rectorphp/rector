<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use PhpParser\Node;
use Rector\Contract\Rector\RectorInterface;
use Rector\ProcessAnalyzer\RectifiedAnalyzer;
use Rector\Skipper\SkipVoter\ClassSkipVoter;
use Rector\Skipper\ValueObject\SkipMatch;
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
    private ClassSkipVoter $classSkipVoter;
    /**
     * @readonly
     */
    private \Rector\Skipper\Skipper\UsedSkipCollector $usedSkipCollector;
    public function __construct(RectifiedAnalyzer $rectifiedAnalyzer, \Rector\Skipper\Skipper\PathSkipper $pathSkipper, ClassSkipVoter $classSkipVoter, \Rector\Skipper\Skipper\UsedSkipCollector $usedSkipCollector)
    {
        $this->rectifiedAnalyzer = $rectifiedAnalyzer;
        $this->pathSkipper = $pathSkipper;
        $this->classSkipVoter = $classSkipVoter;
        $this->usedSkipCollector = $usedSkipCollector;
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElement($element): bool
    {
        return $this->shouldSkipElementAndFilePath($element, __FILE__);
    }
    public function shouldSkipFilePath(string $filePath): bool
    {
        return $this->pathSkipper->shouldSkip($filePath);
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElementAndFilePath($element, string $filePath): bool
    {
        $skipMatch = $this->matchSkip($element, $filePath);
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
        if (!$this->classSkipVoter->match($element)) {
            return null;
        }
        return $this->classSkipVoter->matchSkip($element, $filePath);
    }
    public function markSkipUsed(SkipMatch $skipMatch): void
    {
        $this->usedSkipCollector->markUsed($skipMatch->getSkippedClass(), $skipMatch->getMatchedPath());
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function shouldSkipCurrentNode(string $rectorClass, Node $node): bool
    {
        return $this->rectifiedAnalyzer->hasRectified($rectorClass, $node);
    }
}
