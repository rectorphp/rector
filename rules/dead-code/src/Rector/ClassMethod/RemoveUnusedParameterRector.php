<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeCollector\UnusedParameterResolver;
use Rector\DeadCode\NodeManipulator\MagicMethodDetector;
use Rector\DeadCode\NodeManipulator\VariadicFunctionLikeDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/function.compact.php
 *
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\RemoveUnusedParameterRectorTest
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\OpenSourceRectorTest
 */
final class RemoveUnusedParameterRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var MagicMethodDetector
     */
    private $magicMethodDetector;

    /**
     * @var VariadicFunctionLikeDetector
     */
    private $variadicFunctionLikeDetector;

    /**
     * @var UnusedParameterResolver
     */
    private $unusedParameterResolver;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;

    public function __construct(
        ClassManipulator $classManipulator,
        MagicMethodDetector $magicMethodDetector,
        VariadicFunctionLikeDetector $variadicFunctionLikeDetector,
        UnusedParameterResolver $unusedParameterResolver,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->classManipulator = $classManipulator;
        $this->magicMethodDetector = $magicMethodDetector;
        $this->variadicFunctionLikeDetector = $variadicFunctionLikeDetector;
        $this->unusedParameterResolver = $unusedParameterResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove unused parameter, if not required by interface or parent class',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct($value, $value2)
    {
         $this->value = $value;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct($value)
    {
         $this->value = $value;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($this->classManipulator->hasParentMethodOrInterface($className, $methodName)) {
            return null;
        }

        $childrenOfClass = $this->nodeRepository->findChildrenOfClass($className);
        $unusedParameters = $this->unusedParameterResolver->resolve($node, $methodName, $childrenOfClass);
        if ($unusedParameters === []) {
            return null;
        }

        foreach ($childrenOfClass as $childClassNode) {
            $methodOfChild = $childClassNode->getMethod($methodName);
            if (! $methodOfChild instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }

            $overlappingParameters = $this->getParameterOverlap($methodOfChild->params, $unusedParameters);
            $this->removeNodes($overlappingParameters);
        }

        $this->removeNodes($unusedParameters);

        $this->clearPhpDocInfo($node, $unusedParameters);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if ($classMethod->params === []) {
            return true;
        }

        if ($this->magicMethodDetector->isMagicMethod($classMethod)) {
            return true;
        }

        if ($this->variadicFunctionLikeDetector->isVariadic($classMethod)) {
            return true;
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        // skip interfaces and traits
        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($this->shouldSkipOpenSourceAbstract($classMethod, $classLike)) {
            return true;
        }

        if ($this->shouldSkipOpenSourceEmpty($classMethod)) {
            return true;
        }

        if ($this->shouldSkipOpenSourceProtectedMethod($classMethod)) {
            return true;
        }

        return $this->classNodeAnalyzer->isAnonymousClass($classLike);
    }

    /**
     * @param Param[] $parameters1
     * @param Param[] $parameters2
     * @return Param[]
     */
    private function getParameterOverlap(array $parameters1, array $parameters2): array
    {
        return array_uintersect(
            $parameters1,
            $parameters2,
            function (Param $firstParam, Param $secondParam): int {
                return $this->betterStandardPrinter->areNodesEqual($firstParam, $secondParam) ? 0 : 1;
            }
        );
    }

    /**
     * @param Param[] $unusedParameters
     */
    private function clearPhpDocInfo(ClassMethod $classMethod, array $unusedParameters): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        foreach ($unusedParameters as $unusedParameter) {
            $parameterName = $this->getName($unusedParameter->var);
            if ($parameterName === null) {
                continue;
            }

            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterName);
            if (! $paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }

            if ($paramTagValueNode->parameterName !== '$' . $parameterName) {
                continue;
            }

            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
        }
    }

    private function shouldSkipOpenSourceAbstract(ClassMethod $classMethod, Class_ $class): bool
    {
        // skip as possible contract for 3rd party
        if (! $this->isOpenSourceProjectType()) {
            return false;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        if (! $class->isAbstract()) {
            return false;
        }

        return $classMethod->isPublic();
    }

    private function shouldSkipOpenSourceEmpty(ClassMethod $classMethod): bool
    {
        // skip as possible contract for 3rd party
        if (! $this->isOpenSourceProjectType()) {
            return false;
        }

        return $classMethod->stmts === [] || $classMethod->stmts === null;
    }

    private function shouldSkipOpenSourceProtectedMethod(ClassMethod $classMethod): bool
    {
        // skip as possible contract for 3rd party
        if (! $this->isOpenSourceProjectType()) {
            return false;
        }

        if ($classMethod->isPublic()) {
            return true;
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if ($classLike->isFinal()) {
            return false;
        }

        // can be opened
        return $classMethod->isProtected();
    }
}
