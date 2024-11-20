<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Php81\NodeFactory\EnumFactory;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\Class_\SpatieEnumClassToEnumRector\SpatieEnumClassToEnumRectorTest
 */
final class SpatieEnumClassToEnumRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private EnumFactory $enumFactory;
    /**
     * @var string
     */
    public const TO_UPPER_SNAKE_CASE = 'toUpperSnakeCase';
    private bool $toUpperSnakeCase = \false;
    public function __construct(EnumFactory $enumFactory)
    {
        $this->enumFactory = $enumFactory;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor Spatie enum class to native Enum', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
CODE_SAMPLE
, [self::TO_UPPER_SNAKE_CASE => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Enum_
    {
        if (!$this->isObjectType($node, new ObjectType('Spatie\\Enum\\Enum'))) {
            return null;
        }
        return $this->enumFactory->createFromSpatieClass($node, $this->toUpperSnakeCase);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->toUpperSnakeCase = $configuration[self::TO_UPPER_SNAKE_CASE] ?? \false;
    }
}
