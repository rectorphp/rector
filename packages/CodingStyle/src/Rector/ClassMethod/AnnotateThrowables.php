<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Adds "throws" DocBlock to methods.
 */
final class AnnotateThrowables extends AbstractRector
{
    /**
     * @return string[]
     */
    public function getNodeTypes() : array
    {
        return [Throw_::class];
    }

    /**
     * @param Node|Throw_ $node
     *
     * @return Node|null
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isThrowableAnnotated($node)) {
            return null;
        }

        return $this->annotateMethod($node);
    }

    /**
     * @param Throw_ $node
     *
     * @return bool
     */
    private function isThrowableAnnotated(Throw_ $node) : bool
    {
        $method = $node->getAttribute('methodNode');
        $throwableParts = $node->expr->class->parts;
        $docComment = $method->getDocComment();
        $comments = $method->getComments();

        if (null === $docComment && empty($comments)) {
            return false;
        }

        if ($this->isFQNAnnotated($docComment, $comments, $throwableParts)) {
            return true;
        }

        // No matching condition
        throw new \RuntimeException('This should never happen as all possible conditions should have been predicted.');
    }

    /**
     * @param Doc $docComment
     * @param array<Doc> $comments
     * @param array<string> $throwableParts
     *
     * @return bool
     */
    private function isFQNAnnotated(Doc $docComment, array $comments, array $throwableParts):bool
    {
        $FQN = $this->buildFQN($throwableParts);
        $pattern = $this->buildThrowsDocComment($FQN);

        if ($this->isDocCommentAlreadyPresent($docComment, $pattern)) {
            return  true;
        }

        foreach ($comments as $comment) {
            if ($this->isDocCommentAlreadyPresent($comment, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Doc $docComment
     * @param string                 $pattern
     *
     * @return bool
     */
    private function isDocCommentAlreadyPresent(Doc $docComment, string $pattern):bool
    {
        return false !== strpos($docComment->getText(), $pattern);
    }

    /**
     * @param string $FQNOrThrowableName
     *
     * @return string
     */
    private function buildThrowsDocComment(string $FQNOrThrowableName):string
    {
        return sprintf('@throws %s', $FQNOrThrowableName);
    }

    /**
     * @param array $throwableParts
     *
     * @return string
     */
    private function buildFQN(array $throwableParts):string
    {
        return '\\' . implode('\\', $throwableParts);
    }

    /**
     * @param Throw_ $node
     *
     * @return Throw_
     */
    private function annotateMethod(Throw_ $node):Throw_
    {
        $method = $node->getAttribute('methodNode');
        $throwableParts = $node->expr->class->parts;
        $FQN = $this->buildFQN($throwableParts);
        $parsedDocComment = $this->parseMethodDocComment($method);
        $parsedDocComment[] = $this->buildThrowsDocComment($FQN);
        $rebuiltDocComment = $this->rebuildDocComment($parsedDocComment);

        $method->setAttribute('comments', null);
        $method->setDocComment(new Doc($rebuiltDocComment));

        $node->setAttribute('methodNode', $method);

        return $node;
    }

    /**
     * @param ClassMethod $method
     *
     * @return array
     */
    private function parseMethodDocComment(ClassMethod $method):array
    {
        $parsedDocComment = [];
        if (null !== $method->getDocComment()) {
            throw new \RuntimeException('Implement doc comment parsing');
        }

        return $parsedDocComment;
    }

    /**
     * @param array $docComment
     *
     * @return string
     */
    private function rebuildDocComment(array $docComment):string
    {
        $docComment = array_map(static function($value) {
            return sprintf(" * %s\n", $value);
        }, $docComment);
        $imploded = implode('', $docComment);

        return <<<"PHP"
/**
$imploded
 */
PHP;
    }

    /**
     * From this method documentation is generated.
     */
    public function getDefinition() : RectorDefinition
    {
        return new RectorDefinition(
            'Adds @throws DocBlock comments to methods that thrwo \Throwables.', [
                                                                                   new CodeSample(
                                                                                   // code before
                                                                                       '$user->setPassword("123456");',
                                                                                       // code after
                                                                                       '$user->changePassword("123456");'
                                                                                   ),
                                                                               ]
        );
    }
}
