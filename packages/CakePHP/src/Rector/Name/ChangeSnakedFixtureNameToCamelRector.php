<?php declare(strict_types=1);

namespace Rector\CakePHP\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
 */
final class ChangeSnakedFixtureNameToCamelRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes $fixtues style from snake_case to CamelCase.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeTest
{
    protected $fixtures = [
        'app.posts',
        'app.users',
        'some_plugin.posts/special_posts',
    ];
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeTest
{
    protected $fixtures = [
        'app.Posts',
        'app.Users',
        'some_plugin.Posts/SpeectialPosts',
    ];
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return null;
        }
        if (! $this->isName($node, 'fixtures')) {
            return null;
        }

        foreach ($node->props as $i => $prop) {
            if (! isset($prop->default->items)) {
                continue;
            }
            foreach ($prop->default->items as $j => $item) {
                $node->props[$i]->default->items[$j]->value = $this->renameFixtureName($item->value);
            }
        }

        return $node;
    }

    /**
     * @param String_ $name
     * @return String_
     */
    private function renameFixtureName(String_ $name): String_
    {
        [$prefix, $table] = explode('.', $name->value);
        $table = array_map(
            function ($token): string {
                $tokens = explode('_', $token);

                return implode('', array_map('ucfirst', $tokens));
            },
            explode('/', $table)
        );
        $table = implode('/', $table);

        return new String_("{$prefix}.{$table}", $name->getAttributes());
    }
}
