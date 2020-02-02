<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/questions/10175414/phpunit-dataprovider-simply-doesnt-work/36339907#36339907
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\FixDataProviderAnnotationTypoRector\FixDataProviderAnnotationTypoRectorTest
 */
final class FixDataProviderAnnotationTypoRector extends AbstractPHPUnitRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Fix data provider annotation typos', [
            new CodeSample(
                <<<'PHP'
class SomeClass extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvidor testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }
}
PHP
,
                <<<'PHP'
class SomeClass extends \PHPUnit\Framework\TestCase
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

        $phpDocNode = $phpDocInfo->getPhpDocNode();
        foreach ($phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            $annotationName = $phpDocChildNode->name;
            $annotationName = trim($annotationName, '()@');

            // more than 2 letter difference, probably not a typo
            if (levenshtein($annotationName, 'dataProvider') > 4) {
                continue;
            }

            $phpDocChildNode->name = '@dataProvider ';
        }

        return $node;
    }
}
