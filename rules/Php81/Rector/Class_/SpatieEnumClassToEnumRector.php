<?php

declare(strict_types=1);

namespace Rector\Php81\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Php81\NodeFactory\EnumFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/enumerations
 * @changelog https://github.com/spatie/enum
 *
 * @see \Rector\Tests\Php81\Rector\Class_\SpatieEnumClassToEnumRector\SpatieEnumClassToEnumRectorTest
 */
final class SpatieEnumClassToEnumRector extends AbstractRector
{
    public function __construct(
        private EnumFactory $enumFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor Spatie enum class to native Enum', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use \Spatie\Enum\Enum;

/**
 * @method static self draft()
 * @method static self published()
 * @method static self archived()
 */
class StatusEnum extends Enum
{
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
enum StatusEnum
{
    case draft = 'draft';
    case published = 'published';
    case archived = 'archived';
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, new ObjectType('Spatie\Enum\Enum'))) {
            return null;
        }

        return $this->enumFactory->createFromSpatieClass($node);
    }
}
