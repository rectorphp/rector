<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/class_const_visibility
 *
 * @see \Rector\Php71\Tests\Rector\ClassConst\PublicConstantVisibilityRector\PublicConstantVisibilityRectorTest
 */
final class PublicConstantVisibilityRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add explicit public constant visibility.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const HEY = 'you';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public const HEY = 'you';
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CONSTANT_VISIBILITY)) {
            return null;
        }

        // already non-public
        if (! $node->isPublic()) {
            return null;
        }

        // explicitly public
        if ($node->flags !== 0) {
            return null;
        }

        $this->visibilityManipulator->makePublic($node);

        return $node;
    }
}
