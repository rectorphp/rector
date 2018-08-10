<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Configuration;

use Rector\Exception\ShouldNotHappenException;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @todo try to avoid this and pass to NodeTypeResolver directly
 */
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
        if ($this->splFileInfo === null) {
            throw new ShouldNotHappenException(sprintf(
                '$splFileInfo property was not set. Did you forget to call %s->setCurrentFile() first?',
                self::class
            ));
        }

        return $this->splFileInfo;
    }
}
