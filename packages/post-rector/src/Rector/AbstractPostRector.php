<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\NodeVisitorAbstract;
use Rector\Core\Rector\AbstractRector\NameResolverTrait;
use Rector\Core\Skip\Skipper;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    use NameResolverTrait;

    /**
     * @var SmartFileInfo|null
     */
    protected $smartFileInfo;

    /**
     * @var Skipper
     */
    private $skipper;

    /**
     * @required
     */
    public function autowireAbstractPostRector(Skipper $skipper): void
    {
        $this->skipper = $skipper;
    }

    public function addCurrentSmartFileInfo(?SmartFileInfo $smartFileInfo = null): void
    {
        $this->smartFileInfo = $smartFileInfo;
    }

    protected function shouldSkip(PostRectorInterface $postRector): bool
    {
        return $this->smartFileInfo instanceof SmartFileInfo && $this->skipper->shouldSkipFileInfoAndRule(
            $this->smartFileInfo,
            $postRector
        );
    }
}
