<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Throw_;

use Nette\Utils\FileSystem;
use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\ShortenedObjectType;
use ReflectionFunction;
use ReflectionMethod;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\AnnotateThrowablesRectorTest
 */
final class AnnotateThrowablesRector extends AbstractRector
{
    /**
     * @var string
     */
    private const RETURN_DOCBLOCK_TAG_REGEX = '#@return[ a-zA-Z0-9-_\|\\\t]+#';

    /**
     * @var string
     */
    private const THROWS_DOCBLOCK_TAG_REGEX = '#@throws[ a-zA-Z0-9-_\|\\\t]+#';

    /**
     * @var array
     */
    private $foundThrownClasses = [];

    /** @var \PhpParser\Parser $parser */
    private $parser;

    /**
     * @param \PhpParser\Parser $parser
     */
    public function __construct(\PhpParser\Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Throw_::class, FuncCall::class];
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

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Throw_) {
            if ($this->isThrowableAnnotated($node)) {
                return null;
            }

            $this->annotateThrowable($node);
        }

        if ($node instanceof FuncCall) {
            if (! $this->hasFunctionAnnotatedThrowables($node)) {
                return null;
            }

            $this->annotateThrowablesFromFunctionCall($node);
        }

        return $node;
    }

    private function hasFunctionAnnotatedThrowables(FuncCall $funcCall): bool
    {
        $name = $funcCall->name;
        if (! $name instanceof FullyQualified) {
            return false;
        }

        $functionFqn = implode('\\', $funcCall->name->parts);
        $thrownClasses = $this->extractFunctionThrowsFromDockblock($functionFqn);
        $callee = $this->identifyCalleeFromFunctionCall($funcCall);
        $throwsToAnnotate = [];

        if ($callee instanceof ClassMethod) {
            $class = $callee->getAttribute('className');
            $method = $callee->getAttribute('methodName');

            if (is_string($class) && is_string($method)) {
                $alreadyAnnotatedThrownClasses = $this->extractMethodAnnotatedThrowsFromDocblock($class, $method);

                foreach ($thrownClasses as $thrownClass) {
                    if (false === in_array($thrownClass, $alreadyAnnotatedThrownClasses)) {
                        $throwsToAnnotate[] = $thrownClass;
                    }
                }
            }
        }

        $this->foundThrownClasses = $throwsToAnnotate;

        return ! empty($this->foundThrownClasses);
    }

    private function isThrowableAnnotated(Throw_ $throw): bool
    {
        $phpDocInfo = $this->getThrowingStmtPhpDocInfo($throw);
        $identifiedThrownThrowables = $this->identifyThrownThrowables($throw);

        foreach ($phpDocInfo->getThrowsTypes() as $throwsType) {
            if (! $throwsType instanceof ObjectType) {
                continue;
            }

            $className = $throwsType instanceof ShortenedObjectType
                ? $throwsType->getFullyQualifiedName()
                : $throwsType->getClassName();

            if (! in_array($className, $identifiedThrownThrowables, true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function identifyThrownThrowables(Throw_ $throw): array
    {
        if ($throw->expr instanceof New_) {
            return [$this->getName($throw->expr->class)];
        }

        if ($throw->expr instanceof StaticCall) {
            return $this->identifyThrownThrowablesInStaticCall($throw->expr);
        }

        if ($throw->expr instanceof MethodCall) {
            return $this->identifyThrownThrowablesInMethodCall($throw->expr);
        }

        return [];
    }

    private function identifyThrownThrowablesInMethodCall(MethodCall $methodCall): array
    {
        $thrownClass = $methodCall->var
            ->getAttribute(AttributeKey::FUNCTION_NODE)->name
            ->getAttribute('nextNode')->expr->var
            ->getAttribute('nextNode')->class;

        if (! $thrownClass instanceof FullyQualified) {
            throw new ShouldNotHappenException();
        }

        $classFqn = implode('\\', $thrownClass->parts);
        $methodNode = $methodCall->var->getAttribute('nextNode');
        $methodName = $methodNode->name;

        return $this->extractMethodReturnsFromDocblock($classFqn, $methodName);
    }

    private function identifyThrownThrowablesInStaticCall(StaticCall $staticCall): array
    {
        $thrownClass = $staticCall->class;

        if (! $thrownClass instanceof FullyQualified) {
            throw new ShouldNotHappenException();
        }
        $classFqn = implode('\\', $thrownClass->parts);
        $methodNode = $thrownClass->getAttribute('nextNode');
        $methodName = $methodNode->name;

        return $this->extractMethodReturnsFromDocblock($classFqn, $methodName);
    }

    private function extractMethodReturnsFromDocblock(string $classFqn, string $methodName): array
    {
        $reflectedMethod = new ReflectionMethod($classFqn, $methodName);
        $methodDocblock = $reflectedMethod->getDocComment();

        // copied from https://github.com/nette/di/blob/d1c0598fdecef6d3b01e2ace5f2c30214b3108e6/src/DI/Autowiring.php#L215
        $result = Strings::match((string) $methodDocblock, self::RETURN_DOCBLOCK_TAG_REGEX);
        if ($result === null) {
            return [];
        }

        $returnTags = explode('|', str_replace('@return ', '', $result[0]));
        $returnClasses = [];
        foreach ($returnTags as $returnTag) {
            $returnClasses[] = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
        }

        $this->foundThrownClasses = $returnClasses;

        return $returnClasses;
    }

    private function extractMethodAnnotatedThrowsFromDocblock(string $classFqn, string $methodName):array
    {
        $reflectedMethod = new ReflectionMethod($classFqn, $methodName);
        $methodDocblock = $reflectedMethod->getDocComment();

        // copied from https://github.com/nette/di/blob/d1c0598fdecef6d3b01e2ace5f2c30214b3108e6/src/DI/Autowiring.php#L215
        $result = Strings::matchAll((string) $methodDocblock, self::THROWS_DOCBLOCK_TAG_REGEX);
        if ($result === null || empty($result)) {
            return [];
        }

        $throwTags = array_merge(...$result);
        $thrownClasses = [];
        foreach ($throwTags as $throwTag) {
            $throwTag = str_replace('@throws ', '', $throwTag);
            if (false === class_exists($throwTag)) {
                $throwTag = Reflection::expandClassName($throwTag, $reflectedMethod->getDeclaringClass());
            }

            if (class_exists($throwTag)) {
                $thrownClasses[] = ltrim($throwTag, '\\');
            }
        }

        return $thrownClasses;
    }

    private function extractFunctionThrowsFromDockblock(string $functionFqn): array
    {
        $reflectedFunction = new ReflectionFunction($functionFqn);
        $functionDocblock = $reflectedFunction->getDocComment();

        // copied from https://github.com/nette/di/blob/d1c0598fdecef6d3b01e2ace5f2c30214b3108e6/src/DI/Autowiring.php#L215
        $result = Strings::matchAll((string) $functionDocblock, self::THROWS_DOCBLOCK_TAG_REGEX);
        if (empty($result)) {
            return [];
        }

        $uses = $this->getUsesFromReflectedFunction($reflectedFunction);
        $throwsClasses = [];
        foreach ($result as $throwsTag) {
            $thrownClass = str_replace('@throws ', '', $throwsTag[0]);
            $thrownClass = ltrim($thrownClass, '\\');

            if (! class_exists('\\' . $thrownClass)) {
                $thrownClassParts = explode('\\', $thrownClass);
                $thrownClassShortName = $thrownClassParts[count($thrownClassParts) - 1];

                if (key_exists($thrownClassShortName, $uses)) {
                    $thrownClass = $uses[$thrownClassShortName];
                }
            }

            $throwsClasses[] = $thrownClass;
        }

        return $throwsClasses;
    }

    private function annotateThrowable(Throw_ $node): void
    {
        $throwClass = $this->buildFQN($node);
        if ($throwClass !== null) {
            $this->foundThrownClasses[] = $throwClass;
        }

        if (empty($this->foundThrownClasses)) {
            return;
        }

        foreach ($this->foundThrownClasses as $thrownClass) {
            $docComment = $this->buildThrowsDocComment($thrownClass);

            $throwingStmtPhpDocInfo = $this->getThrowingStmtPhpDocInfo($node);
            $throwingStmtPhpDocInfo->addPhpDocTagNode($docComment);
        }

        $this->foundThrownClasses = [];
    }

    private function annotateThrowablesFromFunctionCall(FuncCall $funcCall): void
    {
        $thrownClasses = $this->foundThrownClasses;
        $this->foundThrownClasses = [];

        if (empty($thrownClasses)) {
            return;
        }

        $calleePhpDocInfo = $this->getCalleeDocBlockFromFuncCall($funcCall);
        if (null === $calleePhpDocInfo) {
            return;
        }

        foreach ($thrownClasses as $thrownClass) {
            $docComment = $this->buildThrowsDocComment($thrownClass);
            $calleePhpDocInfo->addPhpDocTagNode($docComment);
        }
    }

    private function buildThrowsDocComment(string $throwableClass): AttributeAwarePhpDocTagNode
    {
        $throwableClass = ltrim($throwableClass, '\\');
        $genericTagValueNode = new ThrowsTagValueNode(new IdentifierTypeNode('\\' . $throwableClass), '');

        return new AttributeAwarePhpDocTagNode('@throws', $genericTagValueNode);
    }

    private function buildFQN(Throw_ $throw): ?string
    {
        if (! $throw->expr instanceof New_) {
            return null;
        }

        return $this->getName($throw->expr->class);
    }

    private function getThrowingStmtPhpDocInfo(Throw_ $throw): PhpDocInfo
    {
        $method = $throw->getAttribute(AttributeKey::METHOD_NODE);
        $function = $throw->getAttribute(AttributeKey::FUNCTION_NODE);

        /** @var Node|null $stmt */
        $stmt = $method ?? $function ?? null;
        if ($stmt === null) {
            throw new ShouldNotHappenException();
        }

        return $stmt->getAttribute(AttributeKey::PHP_DOC_INFO);
    }

    private function getUsesFromReflectedFunction(ReflectionFunction $reflectionFunction): array
    {
        $functionNode = $this->parseFunction($reflectionFunction);
        if ($functionNode === false) {
            return [];
        }

        $uses = [];
        foreach ($functionNode->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            $use = $stmt->uses[0];
            if (! $use instanceof UseUse) {
                continue;
            }

            $parts = $use->name->parts;
            $uses[$parts[count($parts) - 1]] = implode('\\', $parts);
        }

        return $uses;
    }

    private function getCalleeDocBlockFromFuncCall(FuncCall $funcCall):?PhpDocInfo
    {
        $callee = $this->identifyCalleeFromFunctionCall($funcCall);
        if (null === $callee) {
            return null;
        }

        $calleePhpDocInfo = $callee->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (null === $calleePhpDocInfo) {
            return null;
        }

        return $calleePhpDocInfo;
    }

    private function identifyCalleeFromFunctionCall(FuncCall $funcCall):?ClassMethod
    {
        $callee = $funcCall->getAttribute('previousExpression');
        $i = 0;
        while (true) {
            if ($callee instanceof ClassMethod) {
                break;
            }

            // Anti loop
            if ($i >= 100) {
                return null;
            }

            $i++;
        }

        return $callee;
    }

    private function parseFunction(ReflectionFunction $reflectionFunction):?Namespace_
    {
        $fileName = $reflectionFunction->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        $functionCode = FileSystem::read($fileName);
        if (! is_string($functionCode)) {
            return null;
        }

        $ast = $this->parser->parse($functionCode)[0];

        if (! $ast instanceof Namespace_) {
            return null;
        }

        return $ast;
    }
}
