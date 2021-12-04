<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php81\NodeFactory\EnumFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/enumerations
 * @changelog https://github.com/spatie/enum
 *
 * @see \Rector\Tests\Php81\Rector\Class_\SpatieEnumClassToEnumRector\SpatieEnumClassToEnumRectorTest
 */
final class SpatieEnumClassToEnumRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\EnumFactory
     */
    private $enumFactory;
    public function __construct(\Rector\Php81\NodeFactory\EnumFactory $enumFactory)
    {
        $this->enumFactory = $enumFactory;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ENUM;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor Spatie enum class to native Enum', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
enum StatusEnum : string
{
    case draft = 'draft';
    case published = 'published';
    case archived = 'archived';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Spatie\\Enum\\Enum'))) {
            return null;
        }
        return $this->enumFactory->createFromSpatieClass($node);
    }
}
