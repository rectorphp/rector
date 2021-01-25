<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadDocBlock\TagRemover\VarTagRemover;
use Rector\Naming\VariableRenamer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeResolver\PromotedPropertyResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/constructor_promotion
 * @see https://github.com/php/php-src/pull/5291
 *
 * @see \Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\ClassPropertyAssignToConstructorPromotionRectorTest
 */
final class ClassPropertyAssignToConstructorPromotionRector extends AbstractRector
{
    /**
     * @var PromotedPropertyResolver
     */
    private $promotedPropertyResolver;

    /**
     * @var VariableRenamer
     */
    private $variableRenamer;

    /**
     * @var VarTagRemover
     */
    private $varTagRemover;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;

    public function __construct(
        PromotedPropertyResolver $promotedPropertyResolver,
        VariableRenamer $variableRenamer,
        VarTagRemover $varTagRemover,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->variableRenamer = $variableRenamer;
        $this->varTagRemover = $varTagRemover;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change simple property init and assign to constructor promotion',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public float $someVariable;

    public function __construct(float $someVariable = 0.0)
    {
        $this->someVariable = $someVariable;
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(private float $someVariable = 0.0)
    {
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $promotionCandidates = $this->promotedPropertyResolver->resolveFromClass($node);
        if ($promotionCandidates === []) {
            return null;
        }

        /** @var ClassMethod $constructClassMethod */
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);

        foreach ($promotionCandidates as $promotionCandidate) {
            // does property have some useful annotations?
            $property = $promotionCandidate->getProperty();
            $param = $promotionCandidate->getParam();

            if ($param->variadic) {
                continue;
            }

            $this->removeNode($property);
            $this->removeNode($promotionCandidate->getAssign());

            $property = $promotionCandidate->getProperty();
            $this->decorateParamWithPropertyPhpDocInfo($property, $param);

            /** @var string $oldName */
            $oldName = $this->getName($param->var);

            // property name has higher priority
            $propertyName = $this->getName($property);
            $param->var->name = $propertyName;
            $param->flags = $property->flags;

            // rename also following calls
            $propertyName = $this->getName($property->props[0]);
            $this->variableRenamer->renameVariableInFunctionLike($constructClassMethod, null, $oldName, $propertyName);

            $this->removeClassMethodParam($constructClassMethod, $oldName);
        }

        return $node;
    }

    private function decorateParamWithPropertyPhpDocInfo(Property $property, Param $param): void
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $propertyPhpDocInfo->markAsChanged();

        $param->setAttribute(AttributeKey::PHP_DOC_INFO, $propertyPhpDocInfo);

        // make sure the docblock is useful
        if ($param->type === null) {
            return;
        }

        $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($param, $paramType);
    }

    private function removeClassMethodParam(ClassMethod $classMethod, string $paramName): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $attributeAwareParamTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
        if (! $attributeAwareParamTagValueNode instanceof ParamTagValueNode) {
            return;
        }

        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $attributeAwareParamTagValueNode);
    }
}
