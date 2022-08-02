<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\LNumber;

use RectorPrefix202208\Nette\Utils\DateTime;
use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector\ReplaceTimeNumberWithDateTimeConstantRectorTest
 */
final class ReplaceTimeNumberWithDateTimeConstantRector extends AbstractRector
{
    /**
     * @noRector
     * @var array<int, string>
     */
    private const NUMBER_TO_CONSTANT_NAME = [DateTime::HOUR => 'HOUR', DateTime::DAY => 'DAY', DateTime::WEEK => 'WEEK', DateTime::MONTH => 'MONTH', DateTime::YEAR => 'YEAR'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace time numbers with Nette\\Utils\\DateTime constants', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return 86400;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return \Nette\Utils\DateTime::DAY;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [LNumber::class];
    }
    /**
     * @param LNumber $node
     */
    public function refactor(Node $node) : ?Node
    {
        $number = $node->value;
        $constantName = self::NUMBER_TO_CONSTANT_NAME[$number] ?? null;
        if ($constantName === null) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch('Nette\\Utils\\DateTime', $constantName);
    }
}
