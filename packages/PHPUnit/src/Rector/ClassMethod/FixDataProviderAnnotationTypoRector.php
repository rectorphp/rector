<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
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

        if ($node->getDocComment() === null) {
            return null;
        }

        $textDocComment = $node->getDocComment()->getText();

        $fixedTextDocComment = Strings::replace($textDocComment, '#@(?<annotationName>\w+)#', function (
            $match
        ): ?string {
            // more than 2 letter difference, probably not a typo
            if (levenshtein($match['annotationName'], 'dataProvider') > 4) {
                return null;
            }

            return '@dataProvider';
        });

        if ($textDocComment === $fixedTextDocComment) {
            return null;
        }

        $node->setDocComment(new Doc($fixedTextDocComment));

        return $node;
    }
}
