<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
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
        $method = $node->getAttribute(AttributeKey::METHOD_NODE);

        if ($method === null) {
            throw new RuntimeException('This should not happen and is probably a bug. Please report it.');
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $method->getAttribute(AttributeKey::PHP_DOC_INFO);
        $throwTags = $phpDocInfo->getTagsByName('throws');
        $FQN = $this->buildFQN($node);

        if (empty($throwTags)) {
            return false;
        }

        /** @var AttributeAwarePhpDocTagNode $throwTag */
        foreach ($throwTags as $throwTag) {
            $throwClassName = $throwTag->value->type->name;
            if ($throwClassName === $FQN) {
                return true;
            }

            if (
                ! Strings::contains($throwClassName, '\\') &&
                Strings::contains($FQN, $throwClassName) &&
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
        /** @var ClassMethod $method */
        $method = $node->getAttribute(AttributeKey::METHOD_NODE);
        $FQN = $this->buildFQN($node);
        $docComment = $this->buildThrowsDocComment($FQN);

        /** @var PhpDocInfo $methodPhpDocInfo */
        $methodPhpDocInfo = $method->getAttribute(AttributeKey::PHP_DOC_INFO);
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
}
