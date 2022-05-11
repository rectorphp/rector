<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\DowngradeParentTypeDeclarationRectorTest
 */
final class DowngradeParentTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove "parent" return type, add a "@return parent" tag instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class ParentClass
{
}

class SomeClass extends ParentClass
{
    public function foo(): parent
    {
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentClass
{
}

class SomeClass extends ParentClass
{
    /**
     * @return parent
     */
    public function foo()
    {
        return $this;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        $parentClassReflection = $classReflection->getParentClass();
        if ($parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            $staticType = new \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType($parentClassReflection);
        } else {
            $staticType = new \Rector\StaticTypeMapper\ValueObject\Type\ParentObjectWithoutClassType();
        }
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $staticType)) {
            return null;
        }
        return $node;
    }
}
