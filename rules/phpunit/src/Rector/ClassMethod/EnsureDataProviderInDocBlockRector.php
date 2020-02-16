<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo !== null) {
            return null;
        }

        $comments = $node->getComments();
        if ($comments === []) {
            return null;
        }

        $singleComment = $comments[0];
        if (! Strings::match($singleComment->getText(), '#@dataProvider(\b|s)#')) {
            return null;
        }

        $newCommentText = Strings::replace($singleComment->getText(), '#^\/\*(\s|b)#', '/**$1');
        $comments[0] = new Comment($newCommentText);

        $node->setAttribute('comments', $comments);

        return $node;
    }
}
