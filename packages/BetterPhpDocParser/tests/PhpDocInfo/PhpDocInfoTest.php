<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo;

use Nette\Utils\FileSystem;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocModifier;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class PhpDocInfoTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocInfo
     */
    private $phpDocInfo;

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var PhpDocModifier
     */
    private $phpDocModifier;

    /**
     * @var Node
     */
    private $node;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->phpDocInfo = $this->createPhpDocInfoFromFile(__DIR__ . '/PhpDocInfoSource/doc.txt');

        $this->phpDocInfoPrinter = self::$container->get(PhpDocInfoPrinter::class);
        $this->phpDocModifier = self::$container->get(PhpDocModifier::class);
    }

    public function testHasTag(): void
    {
        $this->assertTrue($this->phpDocInfo->hasTag('param'));
        $this->assertTrue($this->phpDocInfo->hasTag('@throw'));

        $this->assertFalse($this->phpDocInfo->hasTag('random'));
    }

    public function testGetTagsByName(): void
    {
        $paramTags = $this->phpDocInfo->getTagsByName('param');
        $this->assertCount(2, $paramTags);
    }

    public function testParamTypeNode(): void
    {
        $typeNode = $this->phpDocInfo->getParamTypeNode('value');
        $this->assertInstanceOf(TypeNode::class, $typeNode);

        $this->assertSame(
            ['SomeType', 'NoSlash', '\Preslashed', 'null', '\string'],
            $this->phpDocInfo->getParamTypes('value')
        );
    }

    public function testGetVarTypes(): void
    {
        $this->assertSame(['SomeType'], $this->phpDocInfo->getVarTypes());
    }

    public function testReturn(): void
    {
        $this->assertSame(['SomeType'], $this->phpDocInfo->getReturnTypes());
    }

    public function testReplaceTagByAnother(): void
    {
        $phpDocInfo = $this->createPhpDocInfoFromFile(__DIR__ . '/PhpDocInfoSource/test-tag.txt');

        $this->assertFalse($phpDocInfo->hasTag('flow'));
        $this->assertTrue($phpDocInfo->hasTag('test'));

        $this->phpDocModifier->replaceTagByAnother($phpDocInfo->getPhpDocNode(), 'test', 'flow');

        $this->assertFalse($phpDocInfo->hasTag('test'));
        $this->assertTrue($phpDocInfo->hasTag('flow'));

        $this->assertStringEqualsFile(
            __DIR__ . '/PhpDocInfoSource/expected-replaced-tag.txt',
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo)
        );
    }

    private function createPhpDocInfoFromFile(string $path): PhpDocInfo
    {
        $phpDocInfoFactory = self::$container->get(PhpDocInfoFactory::class);
        $phpDocContent = FileSystem::read($path);

        $this->node = new Nop();
        $this->node->setDocComment(new Doc($phpDocContent));

        return $phpDocInfoFactory->createFromNode($this->node);
    }
}
