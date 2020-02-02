<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/questions/6960169/phpunit-dataprovider-issue/9433716#9433716
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\EnsureDataProviderInDocBlockRector\EnsureDataProviderInDocBlockRectorTest
 */
final class EnsureDataProviderInDocBlockRector extends AbstractPHPUnitRector
{
    /**
     * @var string
     */
    private const DOC_BLOCK_OPENING = '/**';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Data provider annotation must be in doc block', [
            new CodeSample(
                <<<'PHP'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /*
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }
}
PHP
,
                <<<'PHP'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInTestClass($node)) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo->hasByName('@dataProvider')) {
            return null;
        }

        if ($phpDocInfo->getOpeningTokenValue() === self::DOC_BLOCK_OPENING) {
            return null;
        }

        $phpDocInfo->changeOpeningTokenValue(self::DOC_BLOCK_OPENING);

        return $node;
    }
}
