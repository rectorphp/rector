<?php declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
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
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @param string[] $classesToDefluent
     */
    public function __construct(array $classesToDefluent, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->classesToDefluent = $classesToDefluent;
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof Variable) {
            return $node;
        }

        if ($node->expr->name !== 'this') {
            return $node;
        }

        if (! $this->isTypes($node->expr, $this->classesToDefluent)) {
            return $node;
        }

        $this->removeNode = true;

        /** @var ClassMethod $methodNode */
        $methodNode = $node->getAttribute(Attribute::METHOD_NODE);
        $this->docBlockAnalyzer->removeTagFromNode($methodNode, 'return');

        return null;
    }
}
