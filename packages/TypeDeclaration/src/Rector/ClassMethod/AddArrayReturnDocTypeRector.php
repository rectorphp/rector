<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\Contract\TypeInferer\FunctionLikeReturnTypeInfererInterface;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\AddArrayReturnDocTypeRectorTest
 */
final class AddArrayReturnDocTypeRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var FunctionLikeReturnTypeInfererInterface[]
     */
    private $functionLikeReturnTypeInferers = [];

    /**
     * @param FunctionLikeReturnTypeInfererInterface[] $functionLikeReturnTypeInferers
     */
    public function __construct(DocBlockManipulator $docBlockManipulator, array $functionLikeReturnTypeInferers)
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->functionLikeReturnTypeInferers = $functionLikeReturnTypeInferers;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds @return annotation to array parameters inferred from the rest of the code', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function getValues(): array
    {
        return $this->values;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $docTypes = [];
        foreach ($this->functionLikeReturnTypeInferers as $functionLikeReturnTypeInferer) {
            $docTypes = $functionLikeReturnTypeInferer->inferFunctionLike($node);
            if ($docTypes !== []) {
                break;
            }
        }

        if ($docTypes !== []) {
            $docType = implode('|', $docTypes);

            $this->docBlockManipulator->addReturnTag($node, $docType);
        }

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if ($classMethod->returnType === null) {
            return true;
        }

        if (! $this->isName($classMethod->returnType, 'array')) {
            return true;
        }

        $returnTypeInfo = $this->docBlockManipulator->getReturnTypeInfo($classMethod);

        // already has return type info, skip
        return $returnTypeInfo !== null;
    }
}
