<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Skipper\Skipper;

use RectorPrefix202208\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @api
 * @see \Symplify\Skipper\Tests\Skipper\Skipper\SkipperTest
 */
final class Skipper
{
    /**
     * @var string
     */
    private const FILE_ELEMENT = 'file_elements';
    /**
     * @var SkipVoterInterface[]
     */
    private $skipVoters;
    /**
     * @param SkipVoterInterface[] $skipVoters
     */
    public function __construct(array $skipVoters)
    {
        $this->skipVoters = $skipVoters;
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElement($element) : bool
    {
        $fileInfo = new SmartFileInfo(__FILE__);
        return $this->shouldSkipElementAndFileInfo($element, $fileInfo);
    }
    public function shouldSkipFileInfo(SmartFileInfo $smartFileInfo) : bool
    {
        return $this->shouldSkipElementAndFileInfo(self::FILE_ELEMENT, $smartFileInfo);
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElementAndFileInfo($element, SmartFileInfo $smartFileInfo) : bool
    {
        foreach ($this->skipVoters as $skipVoter) {
            if ($skipVoter->match($element)) {
                return $skipVoter->shouldSkip($element, $smartFileInfo);
            }
        }
        return \false;
    }
}
