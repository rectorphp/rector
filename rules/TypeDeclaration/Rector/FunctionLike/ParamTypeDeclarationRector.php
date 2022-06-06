<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Rector\FunctionLike;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Interface_;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use RectorPrefix20220606\Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer;
use RectorPrefix20220606\Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix20220606\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use RectorPrefix20220606\Rector\VendorLocker\VendorLockResolver;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints_v5
 * @changelog https://github.com/nikic/TypeUtil
 * @changelog https://github.com/nette/type-fixer
 * @changelog https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 *
 * @deprecated Use specific rules to infer params instead. This rule will be split info many small ones.
 */
final class ParamTypeDeclarationRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    private $vendorLockResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer
     */
    private $traitTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer
     */
    private $controllerRenderMethodAnalyzer;
    public function __construct(VendorLockResolver $vendorLockResolver, ParamTypeInferer $paramTypeInferer, TraitTypeAnalyzer $traitTypeAnalyzer, ParamTagRemover $paramTagRemover, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, ControllerRenderMethodAnalyzer $controllerRenderMethodAnalyzer)
    {
        $this->vendorLockResolver = $vendorLockResolver;
        $this->paramTypeInferer = $paramTypeInferer;
        $this->traitTypeAnalyzer = $traitTypeAnalyzer;
        $this->paramTagRemover = $paramTagRemover;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->controllerRenderMethodAnalyzer = $controllerRenderMethodAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        // why not on Param node? because class like docblock is edited too for @param tags
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change @param types to type declarations if not a BC-break', [new CodeSample(<<<'CODE_SAMPLE'
abstract class VendorParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends VendorParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change($number)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
abstract class VendorParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends VendorParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    public function change(int $number)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        if ($node->params === []) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->controllerRenderMethodAnalyzer->isRenderMethod($node)) {
            return null;
        }
        foreach ($node->params as $position => $param) {
            $this->refactorParam($param, $position, $node);
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function refactorParam(Param $param, int $position, $functionLike) : void
    {
        if ($this->shouldSkipParam($param, $functionLike)) {
            return;
        }
        $inferedType = $this->paramTypeInferer->inferParam($param);
        if ($inferedType instanceof MixedType) {
            return;
        }
        // mixed type cannot be part of union
        if ($inferedType instanceof UnionType && $inferedType->isSuperTypeOf(new MixedType())->yes()) {
            return;
        }
        if ($inferedType instanceof NonExistingObjectType) {
            return;
        }
        if ($this->traitTypeAnalyzer->isTraitType($inferedType)) {
            return;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, TypeKind::PARAM);
        if (!$paramTypeNode instanceof Node) {
            return;
        }
        $parentNode = $functionLike->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Interface_ && $parentNode->extends !== []) {
            return;
        }
        if ($functionLike instanceof ClassMethod && $this->parentClassMethodTypeOverrideGuard->hasParentClassMethodDifferentType($functionLike, $position, $inferedType)) {
            return;
        }
        $param->type = $paramTypeNode;
        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->paramTagRemover->removeParamTagsIfUseless($functionLikePhpDocInfo, $functionLike);
        $this->hasChanged = \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function shouldSkipParam(Param $param, $functionLike) : bool
    {
        if ($param->variadic) {
            return \true;
        }
        if ($this->vendorLockResolver->isClassMethodParamLockedIn($functionLike)) {
            return \true;
        }
        // is nette return type?
        $returnType = $functionLike->returnType;
        if ($returnType instanceof FullyQualified) {
            $objectType = new ObjectType('Nette\\Application\\UI\\Control');
            $returnObjectType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
            if ($objectType->isSuperTypeOf($returnObjectType)->yes()) {
                return \true;
            }
        }
        // no type → check it
        return $param->type !== null;
    }
}
