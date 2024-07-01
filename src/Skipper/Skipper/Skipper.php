<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use PhpParser\Node;
use Rector\Contract\Rector\RectorInterface;
use Rector\ProcessAnalyzer\RectifiedAnalyzer;
use Rector\Skipper\Contract\SkipVoterInterface;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @api
 * @see \Rector\Tests\Skipper\Skipper\SkipperTest
 */
final class Skipper
{
    /**
     * @readonly
     * @var \Rector\ProcessAnalyzer\RectifiedAnalyzer
     */
    private $rectifiedAnalyzer;
    /**
     * @var array<SkipVoterInterface>
     * @readonly
     */
    private $skipVoters;
    /**
     * @readonly
     * @var \Rector\Skipper\Skipper\PathSkipper
     */
    private $pathSkipper;
    /**
     * @param array<SkipVoterInterface> $skipVoters
     */
    public function __construct(RectifiedAnalyzer $rectifiedAnalyzer, array $skipVoters, \Rector\Skipper\Skipper\PathSkipper $pathSkipper)
    {
        $this->rectifiedAnalyzer = $rectifiedAnalyzer;
        $this->skipVoters = $skipVoters;
        $this->pathSkipper = $pathSkipper;
        Assert::allIsInstanceOf($this->skipVoters, SkipVoterInterface::class);
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElement($element) : bool
    {
        return $this->shouldSkipElementAndFilePath($element, __FILE__);
    }
    public function shouldSkipFilePath(string $filePath) : bool
    {
        return $this->pathSkipper->shouldSkip($filePath);
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElementAndFilePath($element, string $filePath) : bool
    {
        foreach ($this->skipVoters as $skipVoter) {
            if (!$skipVoter->match($element)) {
                continue;
            }
            if (!$skipVoter->shouldSkip($element, $filePath)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     * @param string|object $element
     */
    public function shouldSkipCurrentNode($element, string $filePath, string $rectorClass, Node $node) : bool
    {
        if ($this->shouldSkipElementAndFilePath($element, $filePath)) {
            return \true;
        }
        return $this->rectifiedAnalyzer->hasRectified($rectorClass, $node);
    }
}
