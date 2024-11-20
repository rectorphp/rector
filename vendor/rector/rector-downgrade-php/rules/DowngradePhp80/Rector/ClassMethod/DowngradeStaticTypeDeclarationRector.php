<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\StaticType;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\DowngradeStaticTypeDeclarationRectorTest
 */
final class DowngradeStaticTypeDeclarationRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator, ReflectionResolver $reflectionResolver)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove "static" return and param type, add a "@param $this" and "@return $this" tag instead', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getStatic(): static
    {
        return new static();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return static
     */
    public function getStatic()
    {
        return new static();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->params === [] && !$node->returnType instanceof Node) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $staticType = new StaticType($classReflection);
        $hasChanged = \false;
        foreach ($node->getParams() as $param) {
            $hasParamChanged = $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType($param, $node, $staticType);
            if ($hasParamChanged) {
                $hasChanged = \true;
            }
        }
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $staticType)) {
            if ($hasChanged) {
                return $node;
            }
            return null;
        }
        return $node;
    }
}
