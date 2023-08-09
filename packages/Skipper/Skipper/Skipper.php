<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use PhpParser\Node;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ProcessAnalyzer\RectifiedAnalyzer;
use Rector\Skipper\Contract\SkipVoterInterface;
use Rector\Skipper\SkipVoter\ClassSkipVoter;
use Rector\Skipper\SkipVoter\PathSkipVoter;
/**
 * @api
 * @see \Rector\Tests\Skipper\Skipper\Skipper\SkipperTest
 */
final class Skipper
{
    /**
     * @readonly
     * @var \Rector\Core\ProcessAnalyzer\RectifiedAnalyzer
     */
    private $rectifiedAnalyzer;
    /**
     * @var string
     */
    private const FILE_ELEMENT = 'file_elements';
    /**
     * @var SkipVoterInterface[]
     */
    private $skipVoters = [];
    public function __construct(ClassSkipVoter $classSkipVoter, PathSkipVoter $pathSkipVoter, RectifiedAnalyzer $rectifiedAnalyzer)
    {
        $this->rectifiedAnalyzer = $rectifiedAnalyzer;
        $this->skipVoters = [$classSkipVoter, $pathSkipVoter];
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
        return $this->shouldSkipElementAndFilePath(self::FILE_ELEMENT, $filePath);
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
