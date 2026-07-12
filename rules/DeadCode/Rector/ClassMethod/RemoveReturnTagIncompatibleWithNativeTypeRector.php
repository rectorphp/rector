<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveReturnTagIncompatibleWithNativeTypeRector\RemoveReturnTagIncompatibleWithNativeTypeRectorTest
 */
final class RemoveReturnTagIncompatibleWithNativeTypeRector extends AbstractRector
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
        return new RuleDefinition('Remove @return docblock that contradicts the declared native return type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return SomeObject
     */
    public function getName(): string
    {
        return $this->someObject->getName();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getName(): string
    {
        return $this->someObject->getName();
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
        // no native return type to compare against
        if ($node->returnType === null) {
            return null;
        }
        // nothing to remove
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
        // keep any documented explanation
        if ($returnTagValueNode->description !== '') {
            return null;
        }
        if ($this->isClassTypeAlias($node, $returnTagValueNode)) {
            return null;
        }
        $nativeReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);
        if ($this->isReturnTemplate($phpDocInfo, $returnTagValueNode)) {
            return null;
        }
        $docReturnType = $phpDocInfo->getReturnType();
        // a subtype/narrowing is legitimate; only a contradiction is dead
        if (!$nativeReturnType->isSuperTypeOf($docReturnType)->no()) {
            return null;
        }
        if (!$phpDocInfo->removeByType(ReturnTagValueNode::class)) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    private function isClassTypeAlias(Node $node, ReturnTagValueNode $returnTagValueNode): bool
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope || !$scope->isInClass()) {
            return \false;
        }
        $resolvedPhpDocBlock = $scope->getClassReflection()->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return \false;
        }
        $typeAliases = $resolvedPhpDocBlock->getTypeAliasTags() + $resolvedPhpDocBlock->getTypeAliasImportTags();
        if ($typeAliases === []) {
            return \false;
        }
        return $returnTagValueNode->type instanceof IdentifierTypeNode && isset($typeAliases[$returnTagValueNode->type->name]);
    }
    private function isReturnTemplate(PhpDocInfo $phpDocInfo, ReturnTagValueNode $returnTagValueNode): bool
    {
        if (!$returnTagValueNode->type instanceof IdentifierTypeNode) {
            return \false;
        }
        return in_array($returnTagValueNode->type->name, $phpDocInfo->getTemplateNames(), \true);
    }
}
