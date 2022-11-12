<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\FunctionLike\MixedTypeRector\MixedTypeRectorTest
 */
final class MixedTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    public function __construct(ReflectionResolver $reflectionResolver, ClassChildAnalyzer $classChildAnalyzer, ParamTagRemover $paramTagRemover)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->paramTagRemover = $paramTagRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change mixed docs type to mixed typed', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param mixed $param
     */
    public function run($param)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(mixed $param)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassMethod && $this->shouldSkipClassMethod($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->refactorParamTypes($node, $phpDocInfo);
        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $node);
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::MIXED_TYPE;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->classChildAnalyzer->hasChildClassMethod($classReflection, $methodName)) {
            return \true;
        }
        return $this->classChildAnalyzer->hasParentClassMethod($classReflection, $methodName);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function refactorParamTypes($functionLike, PhpDocInfo $phpDocInfo) : void
    {
        foreach ($functionLike->params as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            $paramName = (string) $this->getName($param->var);
            $paramTagValue = $phpDocInfo->getParamTagValueByName($paramName);
            if (!$paramTagValue instanceof ParamTagValueNode) {
                continue;
            }
            $paramType = $phpDocInfo->getParamType($paramName);
            if (!$paramType instanceof MixedType) {
                continue;
            }
            $this->hasChanged = \true;
            $param->type = new Identifier('mixed');
            if ($param->flags !== 0) {
                $param->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }
    }
}
