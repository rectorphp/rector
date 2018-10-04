<?php declare(strict_types=1);

namespace Rector\Php\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PublicConstantVisibilityRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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
     * @param ClassConst $classConstNode
     */
    public function refactor(Node $classConstNode): ?Node
    {
        // already non-public
        if (! $classConstNode->isPublic()) {
            return $classConstNode;
        }

        // explicitly public
        if ($classConstNode->flags !== 0) {
            return $classConstNode;
        }

        $classConstNode->flags = Class_::MODIFIER_PUBLIC;

        return $classConstNode;
    }
}
