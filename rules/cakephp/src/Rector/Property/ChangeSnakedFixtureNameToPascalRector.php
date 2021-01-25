<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CakePHP\Tests\Rector\Property\ChangeSnakedFixtureNameToPascal\ChangeSnakedFixtureNameToPascalTest
 *
 * @see https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
 */
final class ChangeSnakedFixtureNameToPascalRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes $fixtues style from snake_case to PascalCase.', [
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
        'some_plugin.Posts/SpecialPosts',
    ];
CODE_SAMPLE
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
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        if (! $this->isName($node, 'fixtures')) {
            return null;
        }

        foreach ($node->props as $prop) {
            $this->refactorPropertyWithArrayDefault($prop);
        }

        return $node;
    }

    private function refactorPropertyWithArrayDefault(PropertyProperty $propertyProperty): void
    {
        if (! $propertyProperty->default instanceof Array_) {
            return;
        }

        $array = $propertyProperty->default;
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if (! $arrayItem->value instanceof String_) {
                continue;
            }

            $this->renameFixtureName($arrayItem->value);
        }
    }

    private function renameFixtureName(String_ $string): void
    {
        [$prefix, $table] = explode('.', $string->value);

        $tableParts = explode('/', $table);

        $pascalCaseTableParts = array_map(
            function (string $token): string {
                return StaticRectorStrings::underscoreToPascalCase($token);
            },
            $tableParts
        );

        $table = implode('/', $pascalCaseTableParts);

        $string->value = sprintf('%s.%s', $prefix, $table);
    }
}
