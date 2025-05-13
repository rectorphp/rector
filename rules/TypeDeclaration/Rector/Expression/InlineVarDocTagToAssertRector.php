<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Expression;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\PhpDocParser\TypeExpressionFromVarTagResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Expression\InlineVarDocTagToAssertRector\InlineVarDocTagToAssertRectorTest
 */
final class InlineVarDocTagToAssertRector extends AbstractRector implements MinPhpVersionInterface
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
    private TypeExpressionFromVarTagResolver $typeExpressionFromVarTagResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, TypeExpressionFromVarTagResolver $typeExpressionFromVarTagResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->typeExpressionFromVarTagResolver = $typeExpressionFromVarTagResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert inline `@var` tags to calls to `assert()`', [new CodeSample(<<<'CODE_SAMPLE'
/** @var Foo $foo */
$foo = createFoo();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$foo = createFoo();
assert($foo instanceof Foo);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Node[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        if (!$node->expr->var instanceof Variable) {
            return null;
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo || $phpDocInfo->getPhpDocNode()->children === []) {
            return null;
        }
        $expressionVariableName = $node->expr->var->name;
        foreach ($phpDocInfo->getPhpDocNode()->getVarTagValues() as $varTagValueNode) {
            //remove $ from variable name
            $variableName = \substr($varTagValueNode->variableName, 1);
            if ($variableName === $expressionVariableName && $varTagValueNode->description === '') {
                $typeExpression = $this->typeExpressionFromVarTagResolver->resolveTypeExpressionFromVarTag($varTagValueNode->type, new Variable($variableName));
                if ($typeExpression instanceof Expr) {
                    $phpDocInfo->removeByType(VarTagValueNode::class, $variableName);
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                    $arg = new Arg($typeExpression);
                    $funcCall = new FuncCall(new Name('assert'), [$arg]);
                    $expression = new Expression($funcCall);
                    return [$node, $expression];
                }
            }
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STRING_IN_ASSERT_ARG;
    }
}
