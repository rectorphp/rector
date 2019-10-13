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
 * @see https://stackoverflow.com/questions/6960169/phpunit-dataprovider-issue/9433716#9433716
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\EnsureDataProviderInDocBlockRector\EnsureDataProviderInDocBlockRectorTest
 */
final class EnsureDataProviderInDocBlockRector extends AbstractPHPUnitRector
{
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

        if (! $this->hasDataProviderComment($node)) {
            return null;
        }

        $doc = $node->getComments()[0]->getText();
        $doc = Strings::replace($doc, '#^/\*(\s)#', '/**$1');

        $node->setAttribute('comments', null);
        $node->setDocComment(new Doc($doc));

        return $node;
    }

    private function hasDataProviderComment(Node $node): bool
    {
        $docComment = $node->getDocComment();
        if ($docComment !== null) {
            return false;
        }

        $comments = $node->getComments();
        foreach ($comments as $comment) {
            if (Strings::match($comment->getText(), '#@dataProvider\s+\w#')) {
                return true;
            }
        }

        return false;
    }
}
