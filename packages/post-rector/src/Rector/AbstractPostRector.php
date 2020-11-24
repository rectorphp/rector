<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\NodeVisitorAbstract;
use Rector\Core\Rector\AbstractRector\NameResolverTrait;
use Rector\Core\Skip\Skipper;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    use NameResolverTrait;

    /**
     * @var Skipper
     */
    private $skipper;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @required
     */
    public function autowireAbstractPostRector(Skipper $skipper, CurrentFileInfoProvider $currentFileInfoProvider): void
    {
        $this->skipper = $skipper;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    protected function shouldSkip(PostRectorInterface $postRector): bool
    {
        return $this->currentFileInfoProvider->getSmartFileInfo() instanceof SmartFileInfo && $this->skipper->shouldSkipFileInfoAndRule(
            $this->currentFileInfoProvider->getSmartFileInfo(),
            $postRector
        );
    }
}
