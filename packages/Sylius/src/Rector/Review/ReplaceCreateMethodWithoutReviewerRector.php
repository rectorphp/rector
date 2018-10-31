<?php declare(strict_types=1);

namespace Rector\Sylius\Rector\Review;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
        if (! $this->isName($node, 'createForSubjectWithReviewer')) {
            return null;
        }

        if (isset($node->args[1]) && ! $this->isNull($node->args[1]->value)) {
            return null;
        }

        $node->name = new Identifier('createForSubject');

        if (isset($node->args[1])) {
            $node->args = [array_shift($node->args)];
        }

        return $node;
    }
}
