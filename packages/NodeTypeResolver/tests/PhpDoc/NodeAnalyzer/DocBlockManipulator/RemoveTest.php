<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer\DocBlockManipulator;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Nop;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class RemoveTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
        $this->docBlockManipulator = self::$container->get(DocBlockManipulator::class);
    }

    /**
     * @dataProvider provideDataForRemoveTagByName()
     */
    public function testRemoveTagByName(string $phpDocBeforeFilePath, string $phpDocAfter, string $tagName): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromFile($phpDocBeforeFilePath);
        $this->docBlockManipulator->removeTagByName($phpDocInfo, $tagName);

        $this->assertSame($phpDocAfter, $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function provideDataForRemoveTagByName(): Iterator
    {
        yield [__DIR__ . '/RemoveSource/before.txt', '', 'var'];
        yield [__DIR__ . '/RemoveSource/before.txt', '', '@var'];
    }

    /**
     * @dataProvider provideDataForRemoveTagByNameAndContent()
     */
    public function testRemoveTagByNameAndContent(
        string $phpDocBeforeFilePath,
        string $phpDocAfter,
        string $tagName,
        string $tagContent
    ): void {
        $phpDocInfo = $this->createPhpDocInfoFromFile($phpDocBeforeFilePath);
        $this->docBlockManipulator->removeTagByNameAndContent($phpDocInfo, $tagName, $tagContent);

        $this->assertSame($phpDocAfter, $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo));
    }

    public function provideDataForRemoveTagByNameAndContent(): Iterator
    {
        yield [__DIR__ . '/RemoveSource/before2.txt', '', 'method', 'getThis()'];
    }

    public function testRemoveTagByNameAndContentComplex(): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromFile(__DIR__ . '/RemoveSource/before4.txt');

        $this->docBlockManipulator->removeTagByNameAndContent($phpDocInfo, 'method', 'setName');
        $this->docBlockManipulator->removeTagByNameAndContent($phpDocInfo, 'method', 'addItem');
        $this->docBlockManipulator->removeTagByNameAndContent($phpDocInfo, 'method', 'setItems');
        $this->docBlockManipulator->removeTagByNameAndContent($phpDocInfo, 'method', 'setEnabled');

        $this->assertStringEqualsFile(
            __DIR__ . '/RemoveSource/after4.txt',
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo)
        );
    }

    /**
     * @dataProvider provideDataForRemoveParamTagByParameter()
     */
    public function testRemoveParamTagByParameter(
        string $phpDocBeforeFilePath,
        string $phpDocAfterFilePath,
        string $parameterName
    ): void {
        $phpDocInfo = $this->createPhpDocInfoFromFile($phpDocBeforeFilePath);

        $this->docBlockManipulator->removeParamTagByParameter($phpDocInfo, $parameterName);

        $this->assertStringEqualsFile(
            $phpDocAfterFilePath,
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo)
        );
    }

    public function provideDataForRemoveParamTagByParameter(): Iterator
    {
        yield [__DIR__ . '/RemoveSource/before3.txt', __DIR__ . '/RemoveSource/after3.txt', 'paramName'];
        yield [__DIR__ . '/RemoveSource/before3.txt', __DIR__ . '/RemoveSource/after3.txt', '$paramName'];
    }

    private function createPhpDocInfoFromFile(string $phpDocBeforeFilePath): PhpDocInfo
    {
        $phpDocBefore = FileSystem::read($phpDocBeforeFilePath);

        $node = new Nop();
        $node->setDocComment(new Doc($phpDocBefore));

        return $this->phpDocInfoFactory->createFromNode($node);
    }
}
