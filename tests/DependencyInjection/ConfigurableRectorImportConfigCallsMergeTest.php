<?php

declare(strict_types=1);

namespace Rector\Core\Tests\DependencyInjection;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ConfigurableRectorImportConfigCallsMergeTest extends AbstractKernelTestCase
{
    /**
     * @var RenameClassRector
     */
    private $renameClassRector;

    /**
     * @var PrivatesAccessor
     */
    private $privateAccessor;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/main_config.php']);

        $this->renameClassRector = self::$container->get(RenameClassRector::class);
        $this->privateAccessor = new PrivatesAccessor();
    }

    public function test(): void
    {
        $oldToNewClasses = $this->privateAccessor->getPrivateProperty($this->renameClassRector, 'oldToNewClasses');
        $this->assertCount(2, $oldToNewClasses);
    }
}
