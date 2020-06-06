<?php

declare(strict_types=1);

namespace Rector\NetteUtilsCodeQuality\Rector\LNumber;

use Nette\Utils\DateTime;
use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\NetteUtilsCodeQuality\Tests\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector\ReplaceTimeNumberWithDateTimeConstantRectorTest
 */
final class ReplaceTimeNumberWithDateTimeConstantRector extends AbstractRector
{
    /**
     * @noRector
     * @var array<int, string>
     */
    private const NUMBER_TO_CONSTANT_NAME = [
        DateTime::HOUR => 'HOUR',
        DateTime::DAY => 'DAY',
        DateTime::WEEK => 'WEEK',
        DateTime::MONTH => 'MONTH',
        DateTime::YEAR => 'YEAR',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace time numbers with Nette\Utils\DateTime constants', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        return 86400;
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        return \Nette\Utils\DateTime::DAY;
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [LNumber::class];
    }

    /**
     * @param LNumber $node
     */
    public function refactor(Node $node): ?Node
    {
        $number = $node->value;

        $constantName = self::NUMBER_TO_CONSTANT_NAME[$number] ?? null;
        if ($constantName === null) {
            return null;
        }

        return $this->createClassConstFetch('Nette\Utils\DateTime', $constantName);
    }
}
