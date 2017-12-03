<?php declare(strict_types=1);

namespace Rector\YamlParser\DependencyInjection\CompilerPass;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;
use Rector\YamlParser\YamlRectorCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;

final class YamlRetcorCollectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        DefinitionCollector::loadCollectorWithType(
            $containerBuilder,
            YamlRectorCollector::class,
            YamlRectorInterface::class,
            'addYamlRector'
        );
    }
}
