<?php declare(strict_types=1);

namespace Rector\Guard;

use Rector\Exception\NoRectorsLoadedException;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;

final class RectorGuard
{
    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    public function __construct(RectorNodeTraverser $rectorNodeTraverser)
    {
        $this->rectorNodeTraverser = $rectorNodeTraverser;
    }

    public function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->rectorNodeTraverser->getRectorCount()) {
            return;
        }

        throw new NoRectorsLoadedException(
            'No rectors were found. Registers them in rector.yml config to "services:" '
            . 'section, load them via "--config <file>.yml" or "--level <level>" CLI options.'
        );
    }
}
