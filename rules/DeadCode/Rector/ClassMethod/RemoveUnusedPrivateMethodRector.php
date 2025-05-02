<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\RemoveUnusedPrivateMethodRectorTest
 */
final class RemoveUnusedPrivateMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    public function __construct(IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer, ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused private method', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        $classMethods = $node->getMethods();
        if ($classMethods === []) {
            return null;
        }
        $filter = static fn(ClassMethod $classMethod): bool => $classMethod->isPrivate();
        $privateMethods = \array_filter($classMethods, $filter);
        if ($privateMethods === []) {
            return null;
        }
        if ($this->hasDynamicMethodCallOnFetchThis($classMethods)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $collectionTestMethodsUsesPrivateProvider = $this->collectTestMethodsUsesPrivateDataProvider($classReflection, $node, $classMethods);
        $hasChanged = \false;
        foreach ($privateMethods as $privateMethod) {
            if ($this->shouldSkip($privateMethod, $classReflection)) {
                continue;
            }
            if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $privateMethod, $scope)) {
                continue;
            }
            if (\in_array($this->getName($privateMethod), $collectionTestMethodsUsesPrivateProvider, \true)) {
                continue;
            }
            unset($node->stmts[$privateMethod->getAttribute(AttributeKey::STMT_KEY)]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param ClassMethod[] $classMethods
     * @return string[]
     */
    private function collectTestMethodsUsesPrivateDataProvider(ClassReflection $classReflection, Class_ $class, array $classMethods) : array
    {
        if (!$classReflection->is('PHPUnit\\Framework\\TestCase')) {
            return [];
        }
        $privateMethods = [];
        foreach ($classMethods as $classMethod) {
            // test method only public, but may use private data provider
            // so verify @dataProvider and #[\PHPUnit\Framework\Attributes\DataProvider] only on public methods
            if (!$classMethod->isPublic()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
            if ($phpDocInfo instanceof PhpDocInfo && $phpDocInfo->hasByName('dataProvider')) {
                $dataProvider = $phpDocInfo->getByName('dataProvider');
                if ($dataProvider instanceof PhpDocTagNode && $dataProvider->value instanceof GenericTagValueNode) {
                    $dataProviderMethod = $class->getMethod($dataProvider->value->value);
                    if ($dataProviderMethod instanceof ClassMethod && $dataProviderMethod->isPrivate()) {
                        $privateMethods[] = $dataProvider->value->value;
                    }
                }
            }
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, 'PHPUnit\\Framework\\Attributes\\DataProvider')) {
                foreach ($classMethod->attrGroups as $attrGroup) {
                    foreach ($attrGroup->attrs as $attr) {
                        if ($attr->name->toString() === 'PHPUnit\\Framework\\Attributes\\DataProvider') {
                            $argValue = $attr->args[0]->value->value ?? '';
                            if (\is_string($argValue)) {
                                $dataProviderMethod = $class->getMethod($argValue);
                                if ($dataProviderMethod instanceof ClassMethod && $dataProviderMethod->isPrivate()) {
                                    $privateMethods[] = $argValue;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $privateMethods;
    }
    private function shouldSkip(ClassMethod $classMethod, ?ClassReflection $classReflection) : bool
    {
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        // unreliable to detect trait, interface, anonymous class: doesn't make sense
        if ($classReflection->isTrait()) {
            return \true;
        }
        if ($classReflection->isInterface()) {
            return \true;
        }
        if ($classReflection->isAnonymous()) {
            return \true;
        }
        // skip magic methods - @see https://www.php.net/manual/en/language.oop5.magic.php
        if ($classMethod->isMagic()) {
            return \true;
        }
        return $classReflection->hasMethod(MethodName::CALL);
    }
    /**
     * @param ClassMethod[] $classMethods
     */
    private function hasDynamicMethodCallOnFetchThis(array $classMethods) : bool
    {
        foreach ($classMethods as $classMethod) {
            $isFound = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->getStmts(), function (Node $subNode) : bool {
                if (!$subNode instanceof MethodCall) {
                    return \false;
                }
                if (!$subNode->var instanceof Variable) {
                    return \false;
                }
                if (!$this->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $subNode->name instanceof Variable;
            });
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
}
