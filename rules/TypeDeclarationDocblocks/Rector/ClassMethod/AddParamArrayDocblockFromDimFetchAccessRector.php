<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeFinder\ArrayDimFetchFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector\AddParamArrayDocblockFromDimFetchAccessRectorTest
 */
final class AddParamArrayDocblockFromDimFetchAccessRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ArrayDimFetchFinder $arrayDimFetchFinder;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ArrayDimFetchFinder $arrayDimFetchFinder, DocBlockUpdater $docBlockUpdater, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->arrayDimFetchFinder = $arrayDimFetchFinder;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param docblock array type, based on array dim fetch access', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function process(array $data): void
    {
        $item = $data['key'];

        $anotherItem = $data['another_key'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param array<string, mixed> $data
     */
    public function process(array $data): void
    {
        $item = $data['key'];

        $anotherItem = $data['another_key'];
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
        if ($node->getParams() === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasChanged = \false;
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof Node) {
                continue;
            }
            if (!$this->isName($param->type, 'array')) {
                continue;
            }
            $paramName = $this->getName($param);
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
            // already defined, lets skip it
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($paramTagValueNode)) {
                continue;
            }
            $dimFetches = $this->arrayDimFetchFinder->findByVariableName($node, $paramName);
            if ($dimFetches === []) {
                continue;
            }
            // skip for now, not to create more error on incompatible parent @param doc override
            if ($node instanceof ClassMethod && $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($node)) {
                continue;
            }
            $keyTypes = [];
            foreach ($dimFetches as $dimFetch) {
                // nothing to resolve here
                if (!$dimFetch->dim instanceof Expr) {
                    continue;
                }
                $keyTypes[] = $this->getType($dimFetch->dim);
            }
            // most likely not being read
            if ($keyTypes === []) {
                continue;
            }
            if ($this->isOnlyStringType($keyTypes)) {
                $this->createParamTagValueNode($phpDocInfo, $paramName, 'string', $paramTagValueNode);
                $hasChanged = \true;
                continue;
            }
            if ($this->isOnlyIntegerType($keyTypes)) {
                $this->createParamTagValueNode($phpDocInfo, $paramName, 'int', $paramTagValueNode);
                $hasChanged = \true;
                continue;
            }
        }
        if ($hasChanged === \false) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param Type[] $keyTypes
     */
    private function isOnlyStringType(array $keyTypes): bool
    {
        foreach ($keyTypes as $keyType) {
            if ($keyType->isString()->yes()) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    /**
     * @param Type[] $keyTypes
     */
    private function isOnlyIntegerType(array $keyTypes): bool
    {
        foreach ($keyTypes as $keyType) {
            if ($keyType->isInteger()->yes()) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    private function createParamTagValueNode(PhpDocInfo $phpDocInfo, string $paramName, string $keyTypeValue, ?ParamTagValueNode $paramTagValueNode): void
    {
        $arrayGenericTypeNode = new GenericTypeNode(new IdentifierTypeNode('array'), [new IdentifierTypeNode($keyTypeValue), new IdentifierTypeNode('mixed')]);
        if ($paramTagValueNode instanceof ParamTagValueNode) {
            $paramTagValueNode->type = $arrayGenericTypeNode;
        } else {
            $paramTagValueNode = new ParamTagValueNode($arrayGenericTypeNode, \false, '$' . $paramName, '', \false);
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }
    }
}
