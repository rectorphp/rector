<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessUnionReturnDocblockRector\RemoveUselessUnionReturnDocblockRectorTest
 */
final class RemoveUselessUnionReturnDocblockRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, StaticTypeMapper $staticTypeMapper)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove @return union docblock that is broader than a more specific, non-array native return type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return bool|mixed|string
     */
    public function run(): false|string
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): false|string
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
        if ($node->returnType === null) {
            return null;
        }
        // skip as no comments
        if ($node->getComments() === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }
        // keep description, it carries info the native type cannot
        if ($returnTagValueNode->description !== '') {
            return null;
        }
        // only union docblock types are handled here
        if (!$returnTagValueNode->type instanceof UnionTypeNode) {
            return null;
        }
        $nativeReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);
        // keep array native type, the docblock may carry element types
        if ($nativeReturnType->isArray()->yes()) {
            return null;
        }
        $docblockReturnType = $phpDocInfo->getReturnType();
        // the docblock adds nothing when it is broader than the native type
        if (!$docblockReturnType->isSuperTypeOf($nativeReturnType)->yes()) {
            return null;
        }
        if (!$phpDocInfo->removeByType(ReturnTagValueNode::class)) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::UNION_TYPES;
    }
}
