<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CakePHP\Tests\Rector\Property\ChangeSnakedFixtureNameToPascal\ChangeSnakedFixtureNameToPascalTest
 *
 * @see https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
 */
final class ChangeSnakedFixtureNameToPascalRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes $fixtues style from snake_case to PascalCase.', [
            new CodeSample(
                <<<'PHP'
class SomeTest
{
    protected $fixtures = [
        'app.posts',
        'app.users',
        'some_plugin.posts/special_posts',
    ];
PHP
                ,
                <<<'PHP'
class SomeTest
{
    protected $fixtures = [
        'app.Posts',
        'app.Users',
        'some_plugin.Posts/SpecialPosts',
    ];
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return null;
        }
        if (! $this->isName($node, 'fixtures')) {
            return null;
        }

        foreach ($node->props as $prop) {
            if (! $prop->default instanceof Array_) {
                continue;
            }

            foreach ($prop->default->items as $item) {
                if (! $item->value instanceof String_) {
                    continue;
                }

                $this->renameFixtureName($item->value);
            }
        }

        return $node;
    }

    private function renameFixtureName(String_ $string): void
    {
        [$prefix, $table] = explode('.', $string->value);

        $table = array_map(
            function (string $token): string {
                return StaticRectorStrings::underscoreToPascalCase($token);
            },
            explode('/', $table)
        );

        $table = implode('/', $table);

        $string->value = sprintf('%s.%s', $prefix, $table);
    }
}
