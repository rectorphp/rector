<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use Rector\Skipper\Contract\SkipVoterInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @api
 * @see \Rector\Tests\Skipper\Skipper\Skipper\SkipperTest
 */
final class Skipper
{
    /**
     * @var string
     */
    private const FILE_ELEMENT = 'file_elements';
    /**
     * @var SkipVoterInterface[]
     * @readonly
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
     * @param \Symplify\SmartFileSystem\SmartFileInfo|string $smartFileInfo
     */
    public function shouldSkipElementAndFileInfo($element, $smartFileInfo) : bool
    {
        foreach ($this->skipVoters as $skipVoter) {
            if (!$skipVoter->match($element)) {
                continue;
            }
            //            dump(get_class($skipVoter));
            return $skipVoter->shouldSkip($element, $smartFileInfo);
        }
        return \false;
    }
}
