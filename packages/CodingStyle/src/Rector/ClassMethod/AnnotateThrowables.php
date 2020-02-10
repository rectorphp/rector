<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
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
    public function getNodeTypes(): array
    {
        return [Throw_::class];
    }

    /**
     * @param Node|Throw_ $node
     *
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        // Try to get the method in which this throw statement is
        $method = $node->getAttribute('methodNode');

        if ($this->isThrowableAnnotated($method, $node->expr->class->parts)) {
            return null;
        }

        throw new \RuntimeException('We dont know what to do with the node.');
        // return $node if you modified it
        return $node;
    }

    /**
     * @param ClassMethod $method
     * @param array       $throwableParts
     *
     * @return bool
     */
    private function isThrowableAnnotated(ClassMethod $method, array $throwableParts):bool
    {
        if (null === $method->getDocComment() && empty($method->getComments())) {
            return false;
        }

        $fullyQualifiedNamespace = '\\' . implode('\\', $throwableParts);
        $pattern = sprintf('@throws %s', $fullyQualifiedNamespace);
        //$match = strpos($method->getDocComment()->getText(), $pattern);

        //dd($method->getDocComment()->getText(), $fullyQualifiedNamespace, $pattern, $match);
        if (false !== strpos($method->getDocComment()->getText(), $pattern)) {
            return true;
        }

        // No matching condition
        throw new \RuntimeException('This should never happen as all possible conditions should have been predicted.');
    }

    /**
     * From this method documentation is generated.
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change method calls from set* to change*.', [
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
