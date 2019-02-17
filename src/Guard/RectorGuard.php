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
        if ($this->rectorNodeTraverser->getRectorCount() !== 0) {
            return;
        }

        throw new NoRectorsLoadedException(sprintf(
            'We need some rectors to run:%s* register them in rector.yaml under "services:"%s* use "--level <level>"%s* or "--config <file>.yaml"',
            PHP_EOL,
            PHP_EOL,
            PHP_EOL
        ));
    }
}
