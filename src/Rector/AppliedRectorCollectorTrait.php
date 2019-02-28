<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\Application\AppliedRectorCollector;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait AppliedRectorCollectorTrait
{
    /**
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

    /**
     * @required
     */
    public function setAppliedRectorCollector(AppliedRectorCollector $appliedRectorCollector): void
    {
        $this->appliedRectorCollector = $appliedRectorCollector;
    }

    /**
     * @param Node|SmartFileInfo $source
     */
    protected function notifyNodeChangeFileInfo($source): void
    {
        if ($source instanceof Node) {
            $fileInfo = $source->getAttribute(Attribute::FILE_INFO);
            if ($fileInfo === null) {
                // this file was changed before and this is a sub-new node
                // array Traverse to all new nodes would have to be used, but it's not worth the performance
                return;
            }
        } elseif ($source instanceof SmartFileInfo) {
            $fileInfo = $source;
        } else {
            throw new ShouldNotHappenException();
        }

        $this->appliedRectorCollector->addRectorClass(static::class, $fileInfo);
    }
}
