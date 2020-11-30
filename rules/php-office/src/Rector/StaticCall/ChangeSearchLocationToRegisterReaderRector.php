<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#simplified-iofactory
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\ChangeSearchLocationToRegisterReaderRector\ChangeSearchLocationToRegisterReaderRectorTest
 */
final class ChangeSearchLocationToRegisterReaderRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change argument addSearchLocation() to registerReader()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_IOFactory::addSearchLocation($type, $location, $classname);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        \PhpOffice\PhpSpreadsheet\IOFactory::registerReader($type, $classname);
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isStaticCallNamed($node, 'PHPExcel_IOFactory', 'addSearchLocation')) {
            return null;
        }

        $node->class = new FullyQualified('PhpOffice\PhpSpreadsheet\IOFactory');
        $node->name = new Identifier('registerReader');

        // remove middle argument
        $args = $node->args;
        unset($args[1]);

        $node->args = array_values($args);

        return $node;
    }
}
