<?php declare(strict_types=1);

namespace Rector\Tests\RectorDefinition;

use Rector\HttpKernel\RectorKernel;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Testing\PHPUnit\ConfigFactory\AllRectorsConfigFactory;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class GetDefinitionTest extends AbstractKernelTestCase
{
    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    protected function setUp(): void
    {
        $allRectorsConfigFactory = new AllRectorsConfigFactory();
        $config = $allRectorsConfigFactory->create();
        $this->bootKernelWithConfigs(RectorKernel::class, [$config]);

        $this->rectorNodeTraverser = self::$container->get(RectorNodeTraverser::class);
    }

    public function test(): void
    {
        $phpRectors = $this->rectorNodeTraverser->getAllPhpRectors();
        $this->assertGreaterThan(350, count($phpRectors));

        foreach ($phpRectors as $phpRector) {
            $definition = $phpRector->getDefinition();
            $this->assertInstanceOf(RectorDefinition::class, $definition);
        }
    }
}
