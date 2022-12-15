<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\DeprecatedRectorInterface;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix202212\Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Use SplitGroupedClassConstantsRector and SplitGroupedPropertiesRector instead
 */
final class SplitGroupedConstantsAndPropertiesRector extends AbstractRector implements DeprecatedRectorInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Separate constant and properties to own lines', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true, AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt, $isIsThough;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = true;
    const AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt;

    /**
     * @var string
     */
    public $isIsThough;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConst::class, Property::class];
    }
    /**
     * @param ClassConst|Property $node
     * @return Node[]|null
     */
    public function refactor(Node $node) : ?array
    {
        $this->symfonyStyle->error('The "SplitGroupedConstantsAndPropertiesRector" rule is deprecated. Use "SplitGroupedClassConstantsRector" and "SplitGroupedPropertiesRector" instead');
        \sleep(5);
        return null;
    }
}
