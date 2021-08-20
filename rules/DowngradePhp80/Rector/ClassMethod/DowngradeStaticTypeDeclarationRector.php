<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\StaticType;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp71\TypeDeclaration\PhpDocFromTypeDeclarationDecorator;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\DowngradeStaticTypeDeclarationRectorTest
 */
final class DowngradeStaticTypeDeclarationRector extends AbstractRector
{
    public function __construct(
        private PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator,
        private ReflectionProvider $reflectionProvider,
        private AstResolver $astResolver,
        private FamilyRelationsAnalyzer $familyRelationsAnalyzer
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove "static" return and param type, add a "@param $this" and "@return $this" tag instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function getStatic(): static
    {
        return new static();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if ($scope instanceof Scope && $this->shouldSkip($node, $scope)) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            $classReflection = $this->reflectionProvider->getClass($className);
        } else {
            $classReflection = $scope->getClassReflection();
        }

        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $staticType = new StaticType($classReflection->getName());

        foreach ($node->getParams() as $param) {
            $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType($param, $node, $staticType);
        }

        if (! $this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $staticType)) {
            return null;
        }

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod, Scope $scope): bool
    {
        if (! $classMethod->returnType instanceof Name) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($classMethod->returnType, 'self')) {
            return false;
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        $className = $this->nodeNameResolver->getName($classLike);

        if ($className === null) {
            return false;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $children = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);

        foreach ($children as $child) {
            if (! $child->hasMethod($methodName)) {
                continue;
            }

            $method = $child->getMethod($methodName, $scope);
            $classMethod = $this->astResolver->resolveClassMethodFromMethodReflection($method);

            if (! $classMethod instanceof ClassMethod) {
                continue;
            }

            if ($classMethod->returnType === null) {
                return false;
            }
        }

        return true;
    }
}
