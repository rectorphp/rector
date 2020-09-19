<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpDoc;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpDoc\PhpDocTagsFinder;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
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

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->phpDocTagsFinder = self::$container->get(PhpDocTagsFinder::class);
        $this->smartFileSystem = self::$container->get(SmartFileSystem::class);
    }

    public function test(): void
    {
        $docContent = $this->smartFileSystem->readFile(__DIR__ . '/Source/doc_block_throws.txt');

        $throwsTags = $this->phpDocTagsFinder->extractTagsFromStringedDocblock($docContent, 'throws');

        $this->assertCount(3, $throwsTags);
        $this->assertSame(['A', 'B', 'C'], $throwsTags);
    }
}
