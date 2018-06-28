<?php declare(strict_types=1);

namespace Rector\YamlRector\DependencyInjection;

use Rector\YamlRector\Contract\YamlRectorInterface;
use Rector\YamlRector\YamlFileProcessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;

final class YamlRectorCollectorCompilerPass implements CompilerPassInterface
{
    /**
     * @var DefinitionCollector
     */
    private $definitionCollector;

    public function __construct()
    {
        $this->definitionCollector = (new DefinitionCollector(new DefinitionFinder()));
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        $this->definitionCollector->loadCollectorWithType(
            $containerBuilder,
            YamlFileProcessor::class,
            YamlRectorInterface::class,
            'addYamlRector'
        );
    }
}
