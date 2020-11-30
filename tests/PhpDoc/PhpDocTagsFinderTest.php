<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpDoc;

use PhpParser\Node\Stmt\Nop;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpDoc\PhpDocTagsFinder;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

final class PhpDocTagsFinderTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocTagsFinder
     */
    private $phpDocTagsFinder;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->phpDocTagsFinder = self::$container->get(PhpDocTagsFinder::class);
        $this->smartFileSystem = self::$container->get(SmartFileSystem::class);

        // required for parser
        $this->currentNodeProvider = self::$container->get(CurrentNodeProvider::class);
        $this->currentNodeProvider->setNode(new Nop());
    }

    public function test(): void
    {
        $docContent = $this->smartFileSystem->readFile(__DIR__ . '/Source/doc_block_throws.txt');
        $throwsTags = $this->phpDocTagsFinder->extractTrowsTypesFromDocBlock($docContent);

        $this->assertCount(3, $throwsTags);
        $this->assertSame(['A', 'B', 'C'], $throwsTags);
    }
}
