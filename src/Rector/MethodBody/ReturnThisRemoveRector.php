<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\Node\MetadataAttribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use SomeClass;

final class ReturnThisRemoveRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classesToDefluent = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @param string[] $classesToDefluent
     */
    public function __construct(
        array $classesToDefluent,
        NodeTypeResolver $nodeTypeResolver,
        DocBlockAnalyzer $docBlockAnalyzer
    ) {
        $this->classesToDefluent = $classesToDefluent;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes "return $this;" from *fluent interfaces* for specified classes.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction()
    {
    }

    public function otherFunction()
    {
    }
}
CODE_SAMPLE
                ,
                [
                    '$classesToDefluent' => [SomeClass::class],
                ]
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return Return_::class;
    }

    /**
     * @param Return_ $returnNode
     */
    public function refactor(Node $returnNode): ?Node
    {
        if (! $returnNode->expr instanceof Variable) {
            return $returnNode;
        }

        if ($returnNode->expr->name !== 'this') {
            return $returnNode;
        }

        $thisNodeTypes = $this->nodeTypeResolver->resolve($returnNode->expr);
        if (! (bool) array_intersect($thisNodeTypes, $this->classesToDefluent)) {
            return $returnNode;
        }

        $this->removeNode = true;

        $methodNode = $returnNode->getAttribute(MetadataAttribute::METHOD_NODE);
        $this->docBlockAnalyzer->removeTagFromNode($methodNode, 'return');

        return null;
    }
}
