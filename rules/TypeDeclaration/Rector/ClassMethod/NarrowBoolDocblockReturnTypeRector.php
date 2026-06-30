<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\NarrowBoolDocblockReturnTypeRector\NarrowBoolDocblockReturnTypeRectorTest
 */
final class NarrowBoolDocblockReturnTypeRector extends AbstractRector
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
        return new RuleDefinition('Narrow @return docblock "bool" to "false" or "true" when the native return type only allows one of them', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return bool|string[]
     */
    public function run(): false|array
    {
        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return false|string[]
     */
    public function run(): false|array
    {
        return false;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return null|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function refactor(Node $node)
    {
        $constantBoolName = $this->matchNativeConstantBoolName($node);
        if ($constantBoolName === null) {
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
        if (!$returnTagValueNode->type instanceof UnionTypeNode) {
            return null;
        }
        if (!$this->narrowBoolInUnion($returnTagValueNode->type, $constantBoolName)) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * Returns "false" or "true" when the native return type allows exactly one of them, null otherwise.
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function matchNativeConstantBoolName($node): ?string
    {
        $returnType = $node->returnType;
        if (!$returnType instanceof UnionType) {
            return null;
        }
        $hasFalse = \false;
        $hasTrue = \false;
        foreach ($returnType->types as $type) {
            if (!$type instanceof Identifier) {
                continue;
            }
            $lowerName = $type->toLowerString();
            if ($lowerName === 'bool') {
                // ambiguous, both values possible
                return null;
            }
            if ($lowerName === 'false') {
                $hasFalse = \true;
            }
            if ($lowerName === 'true') {
                $hasTrue = \true;
            }
        }
        if ($hasFalse && !$hasTrue) {
            return 'false';
        }
        if ($hasTrue && !$hasFalse) {
            return 'true';
        }
        return null;
    }
    private function narrowBoolInUnion(UnionTypeNode $unionTypeNode, string $constantBoolName): bool
    {
        // already contains the constant? replacing "bool" would create a duplicate
        foreach ($unionTypeNode->types as $typeNode) {
            if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === $constantBoolName) {
                return \false;
            }
        }
        $hasChanged = \false;
        foreach ($unionTypeNode->types as $key => $typeNode) {
            if ($typeNode instanceof IdentifierTypeNode && $typeNode->name === 'bool') {
                $unionTypeNode->types[$key] = new IdentifierTypeNode($constantBoolName);
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
}
