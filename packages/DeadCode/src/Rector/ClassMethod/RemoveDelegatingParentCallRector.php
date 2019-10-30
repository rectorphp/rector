<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionMethod;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\RemoveDelegatingParentCallRectorTest
 */
final class RemoveDelegatingParentCallRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
}
PHP
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
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($this->shouldSkipClass($classNode)) {
            return null;
        }

        if (count((array) $node->stmts) !== 1) {
            return null;
        }

        $onlyStmt = $this->unwrapExpression($node->stmts[0]);

        // are both return?
        if ($this->isMethodReturnType($node, 'void') && ! $onlyStmt instanceof Return_) {
            return null;
        }

        $staticCall = $this->matchStaticCall($onlyStmt);
        if (! $this->isParentCallMatching($node, $staticCall)) {
            return null;
        }

        if ($this->hasRequiredAnnotation($node)) {
            return null;
        }

        // the method is just delegation, nothing extra
        $this->removeNode($node);

        return null;
    }

    private function shouldSkipClass(?ClassLike $classLike): bool
    {
        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($classLike->extends === null) {
            return true;
        }

        return false;
    }

    /**
     * @param Node|Expression $node
     */
    private function unwrapExpression(Node $node): Node
    {
        if ($node instanceof Expression) {
            return $node->expr;
        }

        return $node;
    }

    private function isMethodReturnType(ClassMethod $classMethod, string $type): bool
    {
        if ($classMethod->returnType === null) {
            return false;
        }

        return $this->isName($classMethod->returnType, $type);
    }

    private function matchStaticCall(Node $node): ?StaticCall
    {
        // must be static call
        if ($node instanceof Return_) {
            if ($node->expr instanceof StaticCall) {
                return $node->expr;
            }

            return null;
        }

        if ($node instanceof StaticCall) {
            return $node;
        }

        return null;
    }

    private function isParentCallMatching(ClassMethod $classMethod, ?StaticCall $staticCall): bool
    {
        if ($staticCall === null) {
            return false;
        }

        if (! $this->areNamesEqual($staticCall, $classMethod)) {
            return false;
        }

        if (! $this->isName($staticCall->class, 'parent')) {
            return false;
        }

        if (! $this->areArgsAndParamsEqual($staticCall->args, $classMethod->params)) {
            return false;
        }

        if ($this->isParentClassMethodVisibilityOverride($classMethod, $staticCall)) {
            return false;
        }

        return true;
    }

    private function hasRequiredAnnotation(Node $node): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        $docCommentText = $node->getDocComment()->getText();

        return (bool) Strings::match($docCommentText, '#\s\@required\s#si');
    }

    /**
     * @param Arg[] $args
     * @param Param[] $params
     */
    private function areArgsAndParamsEqual(array $args, array $params): bool
    {
        if (count($args) !== count($params)) {
            return false;
        }

        foreach ($args as $key => $arg) {
            if (! isset($params[$key])) {
                return false;
            }

            $param = $params[$key];
            if (! $this->areNamesEqual($param->var, $arg->value)) {
                return false;
            }
        }

        return true;
    }

    private function isParentClassMethodVisibilityOverride(ClassMethod $classMethod, StaticCall $staticCall): bool
    {
        /** @var string $className */
        $className = $staticCall->getAttribute(AttributeKey::CLASS_NAME);

        $parentClassName = get_parent_class($className);
        if ($parentClassName === false) {
            throw new ShouldNotHappenException();
        }

        /** @var string $methodName */
        $methodName = $this->getName($staticCall);
        $parentClassMethod = $this->parsedNodesByType->findMethod($methodName, $parentClassName);
        if ($parentClassMethod !== null) {
            if ($parentClassMethod->isProtected() && $classMethod->isPublic()) {
                return true;
            }
        }

        // 3rd party code
        if (method_exists($parentClassName, $methodName)) {
            $parentMethodReflection = new ReflectionMethod($parentClassName, $methodName);
            if ($parentMethodReflection->isProtected() && $classMethod->isPublic()) {
                return true;
            }
        }

        return false;
    }
}
