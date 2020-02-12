<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RuntimeException;

/**
 * Adds "throws" DocBlock to methods.
 */
final class AnnotateThrowablesRector extends AbstractRector
{
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

        $this->annotateMethod($node);

        return $node;
    }

    private function isThrowableAnnotated(Throw_ $node): bool
    {
        $stmt = $this->getStmt($node);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $stmt->getAttribute(AttributeKey::PHP_DOC_INFO);
        $alreadyAnnotatedThrowTags = $phpDocInfo->getTagsByName('throws');
        $checkingThrowableClassName = $this->buildFQN($node);

        if (empty($alreadyAnnotatedThrowTags)) {
            return false;
        }

        /** @var AttributeAwarePhpDocTagNode $alreadyAnnotatedThrowTag */
        foreach ($alreadyAnnotatedThrowTags as $alreadyAnnotatedThrowTag) {
            $alreadyAnnotatedThrowTagClassName = $alreadyAnnotatedThrowTag->value->type->name;
            if ($alreadyAnnotatedThrowTagClassName === $checkingThrowableClassName) {
                return true;
            }

            if (
                ! Strings::contains($alreadyAnnotatedThrowTagClassName, '\\') &&
                Strings::contains($checkingThrowableClassName, $alreadyAnnotatedThrowTagClassName) &&
                $this->isThrowableImported($node)
            ) {
                return true;
            }
        }

        return false;
    }

    private function isThrowableImported(Throw_ $node): bool
    {
        $throwClassName = $this->getName($node->expr->class);
        $useNodes = $node->getAttribute(AttributeKey::USE_NODES);

        if ($useNodes === null) {
            return false;
        }

        /** @var Use_ $useNode */
        foreach ($useNodes as $useNode) {
            /** @var UseUse $useStmt */
            foreach ($useNode->uses as $useStmt) {
                if ($this->getName($useStmt) === $throwClassName) {
                    return true;
                }
            }
        }

        return false;
    }

    private function annotateMethod(Throw_ $node): void
    {
        /** @var ClassMethod|Stmt\Function_ $stmt */
        $stmt = $this->getStmt($node);
        $throwClass = $this->buildFQN($node);
        $docComment = $this->buildThrowsDocComment($throwClass);

        /** @var PhpDocInfo $methodPhpDocInfo */
        $methodPhpDocInfo = $stmt->getAttribute(AttributeKey::PHP_DOC_INFO);
        $methodPhpDocInfo->addPhpDocTagNode($docComment);
    }

    private function buildThrowsDocComment(string $FQNOrThrowableName): AttributeAwarePhpDocTagNode
    {
        $value = new AttributeAwareGenericTagValueNode($FQNOrThrowableName);
        return new AttributeAwarePhpDocTagNode('@throws', $value);
    }

    private function buildFQN(Throw_ $node): string
    {
        return '\\' . $this->getName($node->expr->class);
    }

    /**
     * @param Throw_ $node
     *
     * @return ClassMethod|Stmt\Function_
     *
     * @throws ShouldNotHappenException
     */
    private function getStmt(Throw_ $node):Stmt
    {
        $method = $node->getAttribute(AttributeKey::METHOD_NODE);
        $function = $node->getAttribute(AttributeKey::FUNCTION_NODE);
        $stmt = $method ?? $function ?? null;

        if ($stmt === null) {
            throw new ShouldNotHappenException();
        }

        return $stmt;
    }
}
