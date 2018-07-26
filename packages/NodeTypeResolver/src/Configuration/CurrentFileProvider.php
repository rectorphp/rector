<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Configuration;

use Symfony\Component\Finder\SplFileInfo;

final class CurrentFileProvider
{
    /**
     * @var SplFileInfo|null
     */
    private $splFileInfo;

    public function setCurrentFile(SplFileInfo $splFileInfo): void
    {
        $this->splFileInfo = $splFileInfo;
    }

    public function getSplFileInfo(): SplFileInfo
    {
        return $this->splFileInfo;
    }
}
