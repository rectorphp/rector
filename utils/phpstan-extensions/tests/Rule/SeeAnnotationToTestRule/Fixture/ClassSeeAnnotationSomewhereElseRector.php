<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\SeeAnnotationToTestRule\Fixture;

use PhpParser\Node;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://getrector.org
 */
final class ClassSeeAnnotationSomewhereElseRector extends AbstractRector implements PhpRectorInterface
{
    public function getNodeTypes(): array
    {
    }

    public function refactor(Node $node): ?Node
    {
    }

    public function getDefinition(): RectorDefinition
    {
    }
}
