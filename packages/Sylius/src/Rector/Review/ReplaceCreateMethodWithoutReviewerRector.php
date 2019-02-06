<?php declare(strict_types=1);

namespace Rector\Sylius\Rector\Review;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReplaceCreateMethodWithoutReviewerRector extends AbstractRector
{
    /**
     * @var string
     */
    private $reviewFactoryInterface;

    public function __construct(
        string $reviewFactoryInterface = 'Sylius\Component\Review\Factory\ReviewFactoryInterface'
    ) {
        $this->reviewFactoryInterface = $reviewFactoryInterface;
    }

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
        if (! $this->isType($node, $this->reviewFactoryInterface)) {
            return null;
        }

        if (! $this->isName($node, 'createForSubjectWithReviewer')) {
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
