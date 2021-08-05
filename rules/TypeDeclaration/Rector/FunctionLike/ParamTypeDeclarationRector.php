<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
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
use Rector\TypeDeclaration\NodeTypeAnalyzer\TraitTypeAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Rector\TypeDeclaration\ValueObject\NewType;
use Rector\VendorLocker\VendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/scalar_type_hints_v5
 * @changelog https://github.com/nikic/TypeUtil
 * @changelog https://github.com/nette/type-fixer
 * @changelog https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 *
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 */
final class ParamTypeDeclarationRector extends AbstractRector implements MinPhpVersionInterface
{
    public function __construct(
        private VendorLockResolver $vendorLockResolver,
        //private ChildParamPopulator $childParamPopulator,
        private ParamTypeInferer $paramTypeInferer,
        private TraitTypeAnalyzer $traitTypeAnalyzer,
        private ParamTagRemover $paramTagRemover
    ) {
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->params === []) {
            return null;
        }

        // @todo subscribe to Param node directly? narrow scope the better, right?

        foreach ($node->params as $param) {
            $this->refactorParam($param, $node);
        }

        return null;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }

    private function refactorParam(Param $param, ClassMethod | Function_ $functionLike): void
    {
        if ($this->shouldSkipParam($param, $functionLike)) {
            return;
        }

        $inferedType = $this->paramTypeInferer->inferParam($param);
        if ($inferedType instanceof MixedType) {
            return;
        }

        if ($this->traitTypeAnalyzer->isTraitType($inferedType)) {
            return;
        }

        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, TypeKind::PARAM());
        if (! $paramTypeNode instanceof Node) {
            return;
        }

        $parentNode = $functionLike->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Interface_ && $parentNode->extends !== []) {
            return;
        }

        $param->type = $paramTypeNode;

        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->paramTagRemover->removeParamTagsIfUseless($functionLikePhpDocInfo, $functionLike);
    }

    private function shouldSkipParam(Param $param, ClassMethod | Function_ $functionLike): bool
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
