<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\ChildPopulator\ChildParamPopulator;
use Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Rector\TypeDeclaration\ValueObject\NewType;
use Rector\VendorLocker\VendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/scalar_type_hints_v5
 * @see https://github.com/nikic/TypeUtil
 * @see https://github.com/nette/type-fixer
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 */
final class ParamTypeDeclarationRector extends AbstractRector
{
    /**
     * @var ParamTypeInferer
     */
    private $paramTypeInferer;

    /**
     * @var ChildParamPopulator
     */
    private $childParamPopulator;

    /**
     * @var TraitTypeAnalyzer
     */
    private $traitTypeAnalyzer;

    /**
     * @var ParamTagRemover
     */
    private $paramTagRemover;

    /**
     * @var VendorLockResolver
     */
    private $vendorLockResolver;

    public function __construct(
        VendorLockResolver $vendorLockResolver,
        ChildParamPopulator $childParamPopulator,
        ParamTypeInferer $paramTypeInferer,
        TraitTypeAnalyzer $traitTypeAnalyzer,
        ParamTagRemover $paramTagRemover
    ) {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->childParamPopulator = $childParamPopulator;
        $this->traitTypeAnalyzer = $traitTypeAnalyzer;
        $this->paramTagRemover = $paramTagRemover;
        $this->vendorLockResolver = $vendorLockResolver;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change @param types to type declarations if not a BC-break',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
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
                    ,
                    <<<'CODE_SAMPLE'
class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
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
            ),
            ]);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if ($node->params === []) {
            return null;
        }

        foreach ($node->params as $position => $param) {
            $this->refactorParam($param, $node, (int) $position);
        }

        return null;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorParam(Param $param, FunctionLike $functionLike, int $position): void
    {
        if ($this->shouldSkipParam($param, $functionLike, $position)) {
            return;
        }

        $inferedType = $this->paramTypeInferer->inferParam($param);
        if ($inferedType instanceof MixedType) {
            return;
        }

        if ($this->traitTypeAnalyzer->isTraitType($inferedType)) {
            return;
        }

        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $inferedType,
            TypeKind::KIND_PARAM
        );

        if ($paramTypeNode === null) {
            return;
        }

        $parentNode = $functionLike->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Interface_ && $parentNode->extends !== []) {
            return;
        }

        $param->type = $paramTypeNode;

        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->paramTagRemover->removeParamTagsIfUseless($functionLikePhpDocInfo, $functionLike);

        $this->childParamPopulator->populateChildClassMethod($functionLike, $position, $inferedType);
    }

    private function shouldSkipParam(Param $param, FunctionLike $functionLike, int $position): bool
    {
        if ($param->variadic) {
            return true;
        }

        if ($this->vendorLockResolver->isClassMethodParamLockedIn($functionLike)) {
            return true;
        }

        // no type → check it
        if ($param->type === null) {
            return false;
        }

        // already set → skip
        return ! $param->type->getAttribute(NewType::HAS_NEW_INHERITED_TYPE, false);
    }
}
