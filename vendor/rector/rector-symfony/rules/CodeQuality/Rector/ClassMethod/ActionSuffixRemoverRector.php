<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use RectorPrefix202512\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector\ActionSuffixRemoverRectorTest
 */
final class ActionSuffixRemoverRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ControllerMethodAnalyzer $controllerMethodAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ControllerMethodAnalyzer $controllerMethodAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes Action suffixes from methods in Symfony Controllers', [new CodeSample(<<<'CODE_SAMPLE'
class SomeController
{
    public function indexAction()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeController
{
    public function index()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
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
        if (!$this->controllerMethodAnalyzer->isAction($node)) {
            return null;
        }
        if ($node->name->toString() === 'getAction') {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if ($classReflection instanceof ClassReflection && $classReflection->hasNativeMethod(rtrim($node->name->toString(), 'Action'))) {
            return null;
        }
        return $this->removeSuffix($node, 'Action');
    }
    private function removeSuffix(ClassMethod $classMethod, string $suffixToRemove): ?ClassMethod
    {
        $name = $this->getName($classMethod);
        $newName = Strings::replace($name, sprintf('#%s$#', $suffixToRemove), '');
        if ($newName === $name) {
            return null;
        }
        $classMethod->name = new Identifier($newName);
        return $classMethod;
    }
}
