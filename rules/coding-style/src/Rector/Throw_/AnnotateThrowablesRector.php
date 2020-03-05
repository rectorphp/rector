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
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
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
     * @link https://regex101.com/r/oEiq3y/3
     */
    private const RETURN_DOCBLOCK_TAG_REGEX = '#@return[ a-zA-Z0-9_\|\\\t]+#';

    /**
     * @var string
     * @see https://regex101.com/r/fi33R2/1
     */
    private const THROWS_DOCBLOCK_TAG_REGEX = '#@throws[ a-zA-Z0-9_\|\\\t]+#';

    /**
     * @var array
     */
    private $throwablesToAnnotate = [];

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
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
        if ($node instanceof Throw_) {
            $this->analyzeStmtThrow($node);
        }

        if ($node instanceof FuncCall) {
            $this->analyzeStmtFuncCall($node);
        }

        if ($node instanceof MethodCall) {
            $this->analyzeStmtMethodCall($node);
        }

        return count($this->throwablesToAnnotate) > 0;
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

    private function analyzeStmtThrow(Throw_ $throw): void
    {
        $foundThrownThrowables = [];

        // throw new \Throwable
        if ($throw->expr instanceof New_ && $this->getName($throw->expr->class) !== null) {
            $foundThrownThrowables[] = $this->getName($throw->expr->class);
        }

        if ($throw->expr instanceof StaticCall) {
            $foundThrownThrowables = $this->identifyThrownThrowablesInStaticCall($throw->expr);
        }

        if ($throw->expr instanceof MethodCall) {
            $foundThrownThrowables = $this->identifyThrownThrowablesInMethodCall($throw->expr);
        }

        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($throw);

        $this->compareThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function analyzeStmtFuncCall(FuncCall $funcCall): void
    {
        $name = $funcCall->name;
        if (! $name instanceof FullyQualified) {
            return;
        }
        $foundThrownThrowables = $this->extractFunctionAnnotatedThrows($name);
        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($funcCall);
        $this->compareThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function analyzeStmtMethodCall(MethodCall $methodCall): void
    {
        $foundThrownThrowables = $this->identifyThrownThrowablesInMethodCall($methodCall);
        $alreadyAnnotatedThrowables = $this->extractAlreadyAnnotatedThrowables($methodCall);
        $this->compareThrowables($foundThrownThrowables, $alreadyAnnotatedThrowables);
    }

    private function identifyThrownThrowablesInMethodCall(MethodCall $methodCall): array
    {
        $methodClass = $this->getClassFromMethodCall($methodCall);
        $methodName = $methodCall->name;

        if (! $methodClass instanceof FullyQualified) {
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

        if (! $thrownClass instanceof FullyQualified) {
            throw new ShouldNotHappenException();
        }

        return $this->extractMethodReturns($thrownClass, $methodName);
    }

    private function extractMethodReturns(FullyQualified $fullyQualified, Identifier $methodNode): array
    {
        return $this->extractTagsFromMethodDockblock($fullyQualified, $methodNode, '@return');
    }

    private function extractMethodThrows(FullyQualified $fullyQualified, Identifier $methodNode):array
    {
        return $this->extractTagsFromMethodDockblock($fullyQualified, $methodNode, '@throws');
    }

    private function extractTagsFromMethodDockblock(FullyQualified $fullyQualified, Identifier $methodNode, string $tagName):array
    {
        $classFqn = implode('\\', $fullyQualified->parts);
        $methodName = $methodNode->name;

        try {
            $reflectedMethod = new ReflectionMethod($classFqn, $methodName);
        } catch (\Throwable $throwable) {
            return [];
        }

        $methodDocblock = $reflectedMethod->getDocComment();

        if (! is_string($methodDocblock)) {
            return [];
        }

        $returnTags = $this->extractTagFromStringedDocblock($methodDocblock, $tagName);

        $returnClasses = [];
        foreach ($returnTags as $returnTag) {
            $returnClasses[] = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
        }

        return $returnClasses;
    }

    private function extractFunctionAnnotatedThrows(FullyQualified $fullyQualified): array
    {
        $functionFqn = implode('\\', $fullyQualified->parts);
        $reflectedFunction = new ReflectionFunction($functionFqn);
        $functionDocblock = $reflectedFunction->getDocComment();

        if (! is_string($functionDocblock)) {
            return [];
        }

        $annotatedThrownClasses = $this->extractTagFromStringedDocblock($functionDocblock, '@throws');

        return $this->expandClassNamesAnnotatedInFunction($reflectedFunction, $annotatedThrownClasses);
    }

    private function extractTagFromStringedDocblock(string $dockblock, string $tagName): array
    {
        $regEx = null;
        switch ($tagName) {
            case '@throws':
                $regEx = self::THROWS_DOCBLOCK_TAG_REGEX;
                break;
            case '@return':
                $regEx = self::RETURN_DOCBLOCK_TAG_REGEX;
                break;
            default:
                throw new ShouldNotHappenException();
        }
        // copied from https://github.com/nette/di/blob/d1c0598fdecef6d3b01e2ace5f2c30214b3108e6/src/DI/Autowiring.php#L215
        $result = Strings::matchAll($dockblock, $regEx);
        if (empty($result)) {
            return [];
        }

        $matchingTags = array_merge(...$result);
        $explode = static function ($matchingTag) use ($tagName): array {
            // This is required as @return, for example, can be written as "@return ClassOne|ClassTwo|ClassThree"
            return explode('|', str_replace($tagName . ' ', '', $matchingTag));
        };
        $matchingTags = array_map($explode, $matchingTags);

        return array_merge(...$matchingTags);
    }

    private function extractAlreadyAnnotatedThrowables(Node $node): array
    {
        $alreadyAnnotatedThrowables = [];
        $callee = $this->identifyCallee($node);

        if ($callee === null) {
            return $alreadyAnnotatedThrowables;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $callee->getAttribute(AttributeKey::PHP_DOC_INFO);
        foreach ($phpDocInfo->getThrowsTypes() as $throwsType) {
            $thrownClass = null;
            if ($throwsType instanceof ShortenedObjectType) {
                $thrownClass = $throwsType->getFullyQualifiedName();
            }

            if ($throwsType instanceof FullyQualifiedObjectType) {
                $thrownClass = $throwsType->getClassName();
            }

            if ($thrownClass !== null) {
                $alreadyAnnotatedThrowables[] = $thrownClass;
            }
        }

        return $alreadyAnnotatedThrowables;
    }

    private function compareThrowables(array $foundThrownThrowables, array $alreadyAnnotatedThrowables): void
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

        foreach ($foundThrownThrowables as $foundThrownThrowable) {
            if (! in_array($foundThrownThrowable, $alreadyAnnotatedThrowables, true)) {
                $this->throwablesToAnnotate[] = $foundThrownThrowable;
            }
        }
    }

    private function buildThrowsDocComment(string $throwableClass): AttributeAwarePhpDocTagNode
    {
        $genericTagValueNode = new ThrowsTagValueNode(new IdentifierTypeNode($throwableClass), '');

        return new AttributeAwarePhpDocTagNode('@throws', $genericTagValueNode);
    }

    private function identifyCallee(Node $node): ?Node
    {
        $callee = $node->getAttribute('previousExpression');
        while (true) {
            if (
                $callee instanceof ClassMethod ||
                $callee instanceof Function_
            ) {
                break;
            }

            $callee = $callee->getAttribute('previousExpression');
        }

        return $callee;
    }

    private function expandClassNamesAnnotatedInFunction(
        ReflectionFunction $reflectionFunction,
        array $classNames
    ): array {
        $functionNode = $this->reflectionFunctionGetNode($reflectionFunction);
        if (! $functionNode instanceof Namespace_) {
            return [];
        }

        $uses = $this->getUses($functionNode);

        $expandedClasses = [];
        foreach ($classNames as $className) {
            $shortClassName = $this->getShortName($className);
            $expandedClasses[] = $uses[$shortClassName] ?? $className;
        }

        return $expandedClasses;
    }

    private function reflectionFunctionGetNode(ReflectionFunction $reflectionFunction): ?Namespace_
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

    private function getClassFromMethodCall(MethodCall $methodCall):?FullyQualified
    {
        $class = null;
        $previousExpression = $methodCall->getAttribute('previousExpression');

        // [PhpParser\Node\Expr\Assign] $variable = new Class()
        if ($previousExpression instanceof Expression) {
            $class = $previousExpression->expr->expr->class;
        }

        /*
         * method(Param $param)
         * {
         *     $param->method();
         * }
         */
        if ($previousExpression instanceof ClassMethod) {
            $params = $previousExpression->params;
            foreach($params as $param) {
                if ($param->var->name === $methodCall->var->name) {
                    $class = $param->type;
                    break;
                }
            }
        }

        return $class instanceof FullyQualified ? $class : null;
    }

    private function getUses(Namespace_ $node): array
    {
        $uses = [];
        foreach ($node->stmts as $stmt) {
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
}
