<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeTypeResolver\Node\NodeToStringTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CompleteVarDocTypeConstantRector extends AbstractRector
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NodeToStringTypeResolver
     */
    private $nodeToStringTypeResolver;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer, NodeToStringTypeResolver $nodeToStringTypeResolver)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeToStringTypeResolver = $nodeToStringTypeResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Complete constant `@var` annotations for missing one, yet known.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private const NUMBER = 5;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var int
     */
    private const NUMBER = 5;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->docBlockAnalyzer->hasTag($node, 'var')) {
            return null;
        }

        // work only with single-constant
        if (count($node->consts) > 1) {
            return null;
        }

        $knownType = $this->nodeToStringTypeResolver->resolver($node->consts[0]->value);
        if ($knownType === null) {
            return null;
        }

        $this->docBlockAnalyzer->addVarTag($node, $knownType);

        return $node;
    }
}
