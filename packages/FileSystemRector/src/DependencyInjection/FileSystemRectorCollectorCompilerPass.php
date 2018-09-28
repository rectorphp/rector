<?php declare(strict_types=1);

namespace Rector\FileSystemRector\DependencyInjection;

use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\DependencyInjection\DefinitionCollector;
use Symplify\PackageBuilder\DependencyInjection\DefinitionFinder;

final class FileSystemRectorCollectorCompilerPass implements CompilerPassInterface
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
            FileSystemFileProcessor::class,
            FileSystemRectorInterface::class,
            'addFileSystemRector'
        );
    }
}
