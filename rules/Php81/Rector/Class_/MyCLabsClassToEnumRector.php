<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Php81\NodeFactory\EnumFactory;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\MyCLabsClassToEnumRectorTest
 */
final class MyCLabsClassToEnumRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private EnumFactory $enumFactory;
    public function __construct(EnumFactory $enumFactory)
    {
        $this->enumFactory = $enumFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor MyCLabs enum class to native Enum', [new CodeSample(<<<'CODE_SAMPLE'
use MyCLabs\Enum\Enum;

final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum Action : string
{
    case VIEW = 'view';
    case EDIT = 'edit';
}
CODE_SAMPLE
)]);
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
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('MyCLabs\\Enum\\Enum'))) {
            return null;
        }
        return $this->enumFactory->createFromClass($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ENUM;
    }
}
