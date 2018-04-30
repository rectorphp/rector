<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\Tests\NodeAnalyzer;

use PhpParser\Comment\Doc;
use PhpParser\Node\Scalar\String_;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class DocBlockAnalyzerTest extends AbstractContainerAwareTestCase
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

        $this->assertTrue($this->docBlockAnalyzer->hasAnnotation($node, 'param'));
        $this->assertFalse($this->docBlockAnalyzer->hasAnnotation($node, 'var'));
    }

    public function testRemoveAnnotationFromNode(): void
    {
        $node = $this->createNodeWithDoc('@param ParamType $paramName');
        $this->assertNotSame('', $node->getDocComment()->getText());

        $this->docBlockAnalyzer->removeAnnotationFromNode($node, 'param');
        $this->assertNull($node->getDocComment());

        $initDoc = <<<'EOT'
 * @param ParamType $paramName
 * @param AnotherValue $anotherValue
EOT;
        $node = $this->createNodeWithDoc($initDoc);
        $this->docBlockAnalyzer->removeParamTagByName($node, 'paramName');

        $expectedDoc = <<<'EOT'
/**
 * @param AnotherValue $anotherValue
 */
EOT;
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
