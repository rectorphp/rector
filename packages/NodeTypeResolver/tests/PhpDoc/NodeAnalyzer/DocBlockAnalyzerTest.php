<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer;

use PhpParser\Comment\Doc;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverContainerAwareTestCase;
use function Safe\sprintf;

final class DocBlockAnalyzerTest extends AbstractNodeTypeResolverContainerAwareTestCase
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    protected function setUp(): void
    {
        $this->docBlockAnalyzer = $this->container->get(DocBlockAnalyzer::class);
    }

    public function testHasAnnotation(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');

        $this->assertTrue($this->docBlockAnalyzer->hasTag($node, 'param'));
        $this->assertFalse($this->docBlockAnalyzer->hasTag($node, 'var'));
    }

    public function testRemoveAnnotationFromNode(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');
        $this->assertNotSame('', $node->getDocComment()->getText());

        $this->docBlockAnalyzer->removeTagFromNode($node, 'param');
        $this->assertNull($node->getDocComment());

        $initDoc = <<<'CODE_SAMPLE'
 * @param ParamType $paramName
 * @param AnotherValue $anotherValue
CODE_SAMPLE;
        $node = $this->createNodeWithDoc($initDoc);
        $this->docBlockAnalyzer->removeParamTagByName($node, 'paramName');

        $expectedDoc = <<<'CODE_SAMPLE'
/**
 * @param AnotherValue $anotherValue
 */
CODE_SAMPLE;
        $this->assertSame($expectedDoc, $node->getDocComment()->getText());
    }

    public function testGetAnnotationFromNode(): void
    {
        $node = $this->createNodeWithDoc('
           * @var int
           * @deprecated This is deprecated
        ');

        $this->assertSame(['int'], $this->docBlockAnalyzer->getVarTypes($node));
    }

    public function testGetParamTypeFor(): void
    {
        $node = $this->createNodeWithDoc('
           * @param ParamType $paramName
        ');

        $this->assertSame('ParamType', $this->docBlockAnalyzer->getTypeForParam($node, 'paramName'));
    }

    private function createNodeWithDoc(string $doc): String_
    {
        $node = new String_('string');
        $node->setDocComment(new Doc(sprintf('/**%s%s%s */', PHP_EOL, $doc, PHP_EOL)));

        return $node;
    }
}
