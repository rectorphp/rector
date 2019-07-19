<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeToStringResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class VarConstantCommentRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        StaticTypeToStringResolver $staticTypeToStringResolver
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Constant should have a @var comment with type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = 'hi';
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    const HI = 'hi';
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
        if (count($node->consts) > 1) {
            return null;
        }

        $constStaticType = $this->getStaticType($node->consts[0]->value);
        if ($constStaticType === null) {
            return null;
        }

        $staticTypesInStrings = $this->staticTypeToStringResolver->resolveAnyType($constStaticType);

        // nothing we can do
        if ($staticTypesInStrings === []) {
            return null;
        }

        $varTypeInfo = $this->docBlockManipulator->getVarTypeInfo($node);

        if ($varTypeInfo && $varTypeInfo->getTypes() === $staticTypesInStrings) {
            // already set
            return null;
        }

        $this->docBlockManipulator->changeVarTag($node, implode('|', $staticTypesInStrings));

        return $node;
    }
}
