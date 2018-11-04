<?php declare(strict_types=1);

namespace Rector\Guard;

use Rector\Exception\NoRectorsLoadedException;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\YamlRector\YamlFileProcessor;

final class RectorGuard
{
    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var YamlFileProcessor
     */
    private $yamlFileProcessor;

    public function __construct(RectorNodeTraverser $rectorNodeTraverser, YamlFileProcessor $yamlFileProcessor)
    {
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->yamlFileProcessor = $yamlFileProcessor;
    }

    public function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->rectorNodeTraverser->getRectorCount() || $this->yamlFileProcessor->getYamlRectorsCount()) {
            return;
        }

        throw new NoRectorsLoadedException(
            'No rectors were found. Registers them in rector.yml config to "services:" '
            . 'section, load them via "--config <file>.yml" or "--level <level>" CLI options.'
        );
    }
}
