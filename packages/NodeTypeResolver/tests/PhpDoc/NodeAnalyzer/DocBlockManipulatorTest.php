<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer;

use PhpParser\Comment\Doc;
use PhpParser\Node\Scalar\String_;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class DocBlockManipulatorTest extends AbstractKernelTestCase
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->docBlockManipulator = self::$container->get(DocBlockManipulator::class);
    }

    public function testHasAnnotation(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');

        $this->assertTrue($this->docBlockManipulator->hasTag($node, 'param'));
        $this->assertFalse($this->docBlockManipulator->hasTag($node, 'var'));
    }

    public function testRemoveAnnotationFromNode(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');

        $this->assertNotSame('', $node->getDocComment()->getText());

        $this->docBlockManipulator->removeTagFromNode($node, 'param');
        $this->assertNull($node->getDocComment());

        $initDoc = <<<'CODE_SAMPLE'
 * @param ParamType $paramName
 * @param AnotherValue $anotherValue
CODE_SAMPLE;
        $node = $this->createNodeWithDoc($initDoc);

        $this->docBlockManipulator->removeParamTagByName($node, 'paramName');

        $expectedDoc = <<<'CODE_SAMPLE'
/**
 * @param AnotherValue $anotherValue
 */
CODE_SAMPLE;
        $this->assertSame($expectedDoc, $node->getDocComment()->getText());
    }

    private function createNodeWithDoc(string $doc): String_
    {
        $node = new String_('string');
        $node->setDocComment(new Doc(sprintf('/**%s%s%s */', PHP_EOL, $doc, PHP_EOL)));

        return $node;
    }
}
