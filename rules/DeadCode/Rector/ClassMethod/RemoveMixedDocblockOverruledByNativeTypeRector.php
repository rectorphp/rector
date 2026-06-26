<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\Node as PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveMixedDocblockOverruledByNativeTypeRector\RemoveMixedDocblockOverruledByNativeTypeRectorTest
 */
final class RemoveMixedDocblockOverruledByNativeTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove @param mixed and @return mixed docblock that duplicates an already declared native type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function run(int $value): string
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(int $value): string
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip as no comments
        if ($node->getComments() === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $hasChanged = $this->removeMixedParamTags($phpDocInfo, $node);
        $hasChanged = $this->removeMixedReturnTag($phpDocInfo, $node) || $hasChanged;
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function removeMixedParamTags(PhpDocInfo $phpDocInfo, Node $functionLike): bool
    {
        $declaredParamNames = $this->resolveDeclaredParamNames($functionLike);
        if ($declaredParamNames === []) {
            return \false;
        }
        $hasChanged = \false;
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (PhpDocNode $phpDocNode) use ($declaredParamNames, &$hasChanged): ?int {
            if (!$phpDocNode instanceof PhpDocTagNode) {
                return null;
            }
            if ($phpDocNode->name !== '@param') {
                return null;
            }
            if (!$phpDocNode->value instanceof ParamTagValueNode) {
                return null;
            }
            $paramTagValueNode = $phpDocNode->value;
            // keep description, it is the only useful info on a mixed param
            if ($paramTagValueNode->description !== '') {
                return null;
            }
            if (!$this->isMixedType($paramTagValueNode->type)) {
                return null;
            }
            $parameterName = ltrim($paramTagValueNode->parameterName, '$');
            if (!in_array($parameterName, $declaredParamNames, \true)) {
                return null;
            }
            $hasChanged = \true;
            return PhpDocNodeTraverser::NODE_REMOVE;
        });
        return $hasChanged;
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function removeMixedReturnTag(PhpDocInfo $phpDocInfo, Node $functionLike): bool
    {
        if ($functionLike->returnType === null) {
            return \false;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return \false;
        }
        if ($returnTagValueNode->description !== '') {
            return \false;
        }
        if (!$this->isMixedType($returnTagValueNode->type)) {
            return \false;
        }
        return $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
    /**
     * @param ClassMethod|Function_ $functionLike
     * @return string[]
     */
    private function resolveDeclaredParamNames(Node $functionLike): array
    {
        $declaredParamNames = [];
        foreach ($functionLike->params as $param) {
            if (!$param instanceof Param) {
                continue;
            }
            if (!$param->type instanceof Node) {
                continue;
            }
            if (!$param->var instanceof Variable) {
                continue;
            }
            if (!is_string($param->var->name)) {
                continue;
            }
            $declaredParamNames[] = $param->var->name;
        }
        return $declaredParamNames;
    }
    private function isMixedType(TypeNode $typeNode): bool
    {
        return $typeNode instanceof IdentifierTypeNode && $typeNode->name === 'mixed';
    }
}
