<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Throw_;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Throw_;
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
use ReflectionMethod;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\AnnotateThrowablesRectorTest
 */
final class AnnotateThrowablesRector extends AbstractRector
{
    /**
     * @var string
     */
    private const RETURN_DOCBLOCK_TAG_REGEX = '#@return[ a-zA-Z0-9\|\\\t]+#';

    /**
     * @var array
     */
    private $foundThrownClasses = [];

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Throw_::class];
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
     * @param Throw_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isThrowableAnnotated($node)) {
            return null;
        }

        $this->annotateThrowable($node);

        return $node;
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

    private function buildThrowsDocComment(string $throwableClass): AttributeAwarePhpDocTagNode
    {
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
}
