<?php

declare(strict_types=1);

namespace Rector\Sylius\Rector\Review;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Sylius\Component\Review\Factory\ReviewFactoryInterface;

/**
 * @see \Rector\Sylius\Tests\Rector\Review\ReplaceCreateMethodWithoutReviewerRectorTest
 */
final class ReplaceCreateMethodWithoutReviewerRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `createForSubjectWithReviewer()` with null review to standalone method in Sylius',
            [
                new CodeSample(
                    '$this->createForSubjectWithReviewer($subject, null)',
                    '$this->createForSubject($subject)'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, ReviewFactoryInterface::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'createForSubjectWithReviewer')) {
            return null;
        }

        if (isset($node->args[1]) && ! $this->isNull($node->args[1]->value)) {
            return null;
        }

        $node->name = new Identifier('createForSubject');

        if (isset($node->args[1])) {
            /** @var Arg $previousArg */
            $previousArg = array_shift($node->args);
            $node->args = [$previousArg];
        }

        return $node;
    }
}
