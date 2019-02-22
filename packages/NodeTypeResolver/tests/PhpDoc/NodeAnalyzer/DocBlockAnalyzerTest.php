<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer;

use PhpParser\Comment\Doc;
use PhpParser\Node\Scalar\String_;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\Node\CurrentNodeProvider;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class DocBlockAnalyzerTest extends AbstractKernelTestCase
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->docBlockAnalyzer = self::$container->get(DocBlockAnalyzer::class);
        $this->currentNodeProvider = self::$container->get(CurrentNodeProvider::class);
    }

    public function testHasAnnotation(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');
        $this->currentNodeProvider->setNode($node);

        $this->assertTrue($this->docBlockAnalyzer->hasTag($node, 'param'));
        $this->assertFalse($this->docBlockAnalyzer->hasTag($node, 'var'));
    }

    public function testRemoveAnnotationFromNode(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');
        $this->currentNodeProvider->setNode($node);

        $this->assertNotSame('', $node->getDocComment()->getText());

        $this->docBlockAnalyzer->removeTagFromNode($node, 'param');
        $this->assertNull($node->getDocComment());

        $initDoc = <<<'CODE_SAMPLE'
 * @param ParamType $paramName
 * @param AnotherValue $anotherValue
CODE_SAMPLE;
        $node = $this->createNodeWithDoc($initDoc);
        $this->currentNodeProvider->setNode($node);

        $this->docBlockAnalyzer->removeParamTagByName($node, 'paramName');

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
        $this->currentNodeProvider->setNode($node);

        $node->setDocComment(new Doc(sprintf('/**%s%s%s */', PHP_EOL, $doc, PHP_EOL)));

        return $node;
    }
}
