<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Throw_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Throw_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpDoc\PhpDocTagsFinder;
use Rector\Core\PhpParser\Node\Value\ClassResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Reflection\ClassMethodReflectionHelper;
use Rector\Core\Reflection\FunctionReflectionHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionFunction;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\AnnotateThrowablesRectorTest
 */
final class AnnotateThrowablesRector extends AbstractRector
{
    /**
     * @var array
     */
    private $throwablesToAnnotate = [];

    /**
     * @var FunctionReflectionHelper
     */
    private $functionReflectionHelper;

    /**
     * @var ClassMethodReflectionHelper
     */
    private $classMethodReflectionHelper;

    /**
     * @var ClassResolver
     */
    private $classResolver;

    /**
     * @var PhpDocTagsFinder
     */
    private $phpDocTagsFinder;

    public function __construct(
        ClassMethodReflectionHelper $classMethodReflectionHelper,
        ClassResolver $classResolver,
        FunctionReflectionHelper $functionReflectionHelper,
        PhpDocTagsFinder $phpDocTagsFinder
    ) {
        $this->functionReflectionHelper = $functionReflectionHelper;
        $this->classMethodReflectionHelper = $classMethodReflectionHelper;
        $this->classResolver = $classResolver;
        $this->phpDocTagsFinder = $phpDocTagsFinder;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Throw_::class, FuncCall::class, MethodCall::class];
    }

    /**
     * From this method documentation is generated.
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds @throws DocBlock comments to methods that thrwo \Throwables.', [
                new CodeSample(
                // code before
                    <<<'PHP'
class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     */
    public function throwException(int $code)
    {
        throw new \RuntimeException('', $code);
    }
}
PHP
                    ,
                    // code after
                    <<<'PHP'
class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     * @throws \RuntimeException
     */
    public function throwException(int $code)
    {
        throw new \RuntimeException('', $code);
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @param Node|Throw_|MethodCall|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->throwablesToAnnotate = [];
        if ($this->hasThrowablesToAnnotate($node)) {
            $this->annotateThrowables($node);
            return $node;
        }

        return null;
    }

    private function hasThrowablesToAnnotate(Node $node): bool
    {
        $foundThrowables = 0;
        if ($node instanceof Throw_) {
            $foundThrowables = $this->analyzeStmtThrow($node);
        }

        if ($node instanceof FuncCall) {
            $foundThrowables = $this->analyzeStmtFuncCall($node);
        }

        if ($node instanceof MethodCall) {
            $foundThrowables = $this->analyzeStmtMethodCall($node);
        }

        return $foundThrowables > 0;
    }

    private function annotateThrowables(Node $node): void
    {
        $callee = $this->identifyCallee($node);

        if ($callee === null) {
            return;
        }

        $phpDocInfo = $callee->getAttribute(AttributeKey::PHP_DOC_INFO);
        foreach ($this->throwablesToAnnotate as $throwableToAnnotate) {
            $docComment = $this->buildThrowsDocComment($throwableToAnnotate);
            $phpDocInfo->addPhpDocTagNode($docComment);
        }
    }

    private function analyzeStmtThrow(Throw_ $throw): int
    {
        $foundThrownThrowables = [];

        // throw new \Throwable
        if ($throw->expr instanceof New_) {
            $class = $this->getName($throw->expr->class);
            if ($class !== null) {
                $foundThrownThrowables[] = $class;
            }
        }

        if ($throw->expr instanceof StaticCall) {
            $foundThrownThrowables = $this->identifyThrownThrowablesInStaticCall($throw->expr);
        }

        if ($throw->expr instanceof MethodCall) {
            $foundThrownThrowables = $this->identifyThrownThrowablesInMethodCall($throw->expr);
        }

        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($throw);

        return $this->diffThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function analyzeStmtFuncCall(FuncCall $funcCall): int
    {
        $functionFqn = $this->getName($funcCall);

        if ($functionFqn === null) {
            return 0;
        }

        $reflectedFunction = new ReflectionFunction($functionFqn);
        $foundThrownThrowables = $this->functionReflectionHelper->extractFunctionAnnotatedThrows($reflectedFunction);
        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($funcCall);
        return $this->diffThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function analyzeStmtMethodCall(MethodCall $methodCall): int
    {
        $foundThrownThrowables = $this->identifyThrownThrowablesInMethodCall($methodCall);
        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($methodCall);
        return $this->diffThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function identifyThrownThrowablesInMethodCall(MethodCall $methodCall): array
    {
        $methodClass = $this->classResolver->getClassFromMethodCall($methodCall);
        $methodName = $methodCall->name;

        if (! $methodClass instanceof FullyQualified || ! $methodName instanceof Identifier) {
            return [];
        }

        return $methodCall->getAttribute('parentNode') instanceof Throw_
            ? $this->extractMethodReturns($methodClass, $methodName)
            : $this->extractMethodThrows($methodClass, $methodName);
    }

    private function identifyThrownThrowablesInStaticCall(StaticCall $staticCall): array
    {
        $thrownClass = $staticCall->class;
        $methodName = $thrownClass->getAttribute('nextNode');

        if (! $thrownClass instanceof FullyQualified || ! $methodName instanceof Identifier) {
            throw new ShouldNotHappenException();
        }

        return $this->extractMethodReturns($thrownClass, $methodName);
    }

    private function extractMethodReturns(FullyQualified $fullyQualified, Identifier $identifier): array
    {
        $method = $identifier->name;
        $class = $this->getName($fullyQualified);

        if ($class === null) {
            return [];
        }

        return $this->classMethodReflectionHelper->extractTagsFromMethodDockblock($class, $method, '@return');
    }

    private function extractMethodThrows(FullyQualified $fullyQualified, Identifier $identifier): array
    {
        $method = $identifier->name;
        $class = $this->getName($fullyQualified);

        if ($class === null) {
            return [];
        }

        return $this->classMethodReflectionHelper->extractTagsFromMethodDockblock($class, $method, '@throws');
    }

    private function extractAlreadyAnnotatedThrowables(Node $node): array
    {
        $callee = $this->identifyCallee($node);

        return $callee === null ? [] : $this->phpDocTagsFinder->extractTagsThrowsFromNode($callee);
    }

    private function diffThrowables(array $foundThrownThrowables, array $alreadyAnnotatedThrowables): int
    {
        $normalizeNamespace = static function (string $class): string {
            $class = ltrim($class, '\\');
            return '\\' . $class;
        };

        $foundThrownThrowables = array_map($normalizeNamespace, $foundThrownThrowables);
        $alreadyAnnotatedThrowables = array_map($normalizeNamespace, $alreadyAnnotatedThrowables);

        $filterClasses = static function (string $class): bool {
            return class_exists($class) || interface_exists($class);
        };

        $foundThrownThrowables = array_filter($foundThrownThrowables, $filterClasses);
        $alreadyAnnotatedThrowables = array_filter($alreadyAnnotatedThrowables, $filterClasses);

        $this->throwablesToAnnotate = array_diff($foundThrownThrowables, $alreadyAnnotatedThrowables);

        return count($this->throwablesToAnnotate);
    }

    private function buildThrowsDocComment(string $throwableClass): AttributeAwarePhpDocTagNode
    {
        $genericTagValueNode = new ThrowsTagValueNode(new IdentifierTypeNode($throwableClass), '');

        return new AttributeAwarePhpDocTagNode('@throws', $genericTagValueNode);
    }

    private function identifyCallee(Node $node): ?Node
    {
        return $this->betterNodeFinder->findFirstAncestorInstancesOf($node, [ClassMethod::class, Function_::class]);
    }
}
