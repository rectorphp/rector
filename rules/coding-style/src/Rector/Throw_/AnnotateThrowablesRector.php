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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ClassResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Reflection\ClassMethodReflectionHelper;
use Rector\Core\Reflection\FunctionAnnotationResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionFunction;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\AnnotateThrowablesRectorTest
 */
final class AnnotateThrowablesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $throwablesToAnnotate = [];

    /**
     * @var FunctionAnnotationResolver
     */
    private $functionAnnotationResolver;

    /**
     * @var ClassMethodReflectionHelper
     */
    private $classMethodReflectionHelper;

    /**
     * @var ClassResolver
     */
    private $classResolver;

    public function __construct(
        ClassMethodReflectionHelper $classMethodReflectionHelper,
        ClassResolver $classResolver,
        FunctionAnnotationResolver $functionAnnotationResolver
    ) {
        $this->functionAnnotationResolver = $functionAnnotationResolver;
        $this->classMethodReflectionHelper = $classMethodReflectionHelper;
        $this->classResolver = $classResolver;
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
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                    ,
                    // code after
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Throw_|MethodCall|FuncCall $node
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
        $callee = $this->identifyCaller($node);

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

        $reflectionFunction = new ReflectionFunction($functionFqn);
        $foundThrownThrowables = $this->functionAnnotationResolver->extractFunctionAnnotatedThrows($reflectionFunction);
        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($funcCall);
        return $this->diffThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function analyzeStmtMethodCall(MethodCall $methodCall): int
    {
        $foundThrownThrowables = $this->identifyThrownThrowablesInMethodCall($methodCall);
        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($methodCall);
        return $this->diffThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function identifyCaller(Node $node): ?Node
    {
        return $this->betterNodeFinder->findFirstAncestorInstancesOf($node, [ClassMethod::class, Function_::class]);
    }

    private function buildThrowsDocComment(string $throwableClass): AttributeAwarePhpDocTagNode
    {
        $throwsTagValueNode = new ThrowsTagValueNode(new IdentifierTypeNode($throwableClass), '');

        return new AttributeAwarePhpDocTagNode('@throws', $throwsTagValueNode);
    }

    /**
     * @return array<class-string>
     */
    private function identifyThrownThrowablesInStaticCall(StaticCall $staticCall): array
    {
        $thrownClass = $staticCall->class;
        $methodName = $thrownClass->getAttribute(AttributeKey::NEXT_NODE);

        if (! $thrownClass instanceof FullyQualified || ! $methodName instanceof Identifier) {
            throw new ShouldNotHappenException();
        }

        return $this->extractMethodReturns($thrownClass, $methodName);
    }

    /**
     * @return class-string[]
     */
    private function identifyThrownThrowablesInMethodCall(MethodCall $methodCall): array
    {
        $fullyQualified = $this->classResolver->getClassFromMethodCall($methodCall);
        $methodName = $methodCall->name;

        if (! $fullyQualified instanceof FullyQualified || ! $methodName instanceof Identifier) {
            return [];
        }

        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

        return $parent instanceof Throw_ ? $this->extractMethodReturns($fullyQualified, $methodName)
            : $this->extractMethodThrows($fullyQualified, $methodName);
    }

    /**
     * @return class-string[]
     */
    private function extractAlreadyAnnotatedThrowables(Node $node): array
    {
        $callee = $this->identifyCaller($node);
        if ($callee === null) {
            return [];
        }

        /** @var PhpDocInfo|null $callePhpDocInfo */
        $callePhpDocInfo = $callee->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($callePhpDocInfo === null) {
            return [];
        }

        return $callePhpDocInfo->getThrowsClassNames();
    }

    /**
     * @param string[] $foundThrownThrowables
     * @param string[] $alreadyAnnotatedThrowables
     */
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

    /**
     * @return class-string[]
     */
    private function extractMethodReturns(FullyQualified $fullyQualified, Identifier $identifier): array
    {
        $method = $identifier->name;
        $class = $this->getName($fullyQualified);

        if ($class === null) {
            return [];
        }

        return $this->classMethodReflectionHelper->extractTagsFromMethodDockblock($class, $method, '@return');
    }

    /**
     * @return class-string[]
     */
    private function extractMethodThrows(FullyQualified $fullyQualified, Identifier $identifier): array
    {
        $method = $identifier->name;
        $class = $this->getName($fullyQualified);

        if ($class === null) {
            return [];
        }

        return $this->classMethodReflectionHelper->extractTagsFromMethodDockblock($class, $method, '@throws');
    }
}
