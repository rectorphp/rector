<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix202208\Symfony\Component\String\UnicodeString;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\CakePHP\Tests\Rector\Property\ChangeSnakedFixtureNameToPascal\ChangeSnakedFixtureNameToPascalTest
 *
 * @see https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
 */
final class ChangeSnakedFixtureNameToPascalRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes $fixtures style from snake_case to PascalCase.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest
{
    protected $fixtures = [
        'app.posts',
        'app.users',
        'some_plugin.posts/special_posts',
    ];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest
{
    protected $fixtures = [
        'app.Posts',
        'app.Users',
        'some_plugin.Posts/SpecialPosts',
    ];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        if (!$this->isName($node, 'fixtures')) {
            return null;
        }
        foreach ($node->props as $prop) {
            $this->refactorPropertyWithArrayDefault($prop);
        }
        return $node;
    }
    private function refactorPropertyWithArrayDefault(PropertyProperty $propertyProperty) : void
    {
        if (!$propertyProperty->default instanceof Array_) {
            return;
        }
        $array = $propertyProperty->default;
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            $itemValue = $arrayItem->value;
            if (!$itemValue instanceof String_) {
                continue;
            }
            $this->renameFixtureName($itemValue);
        }
    }
    private function renameFixtureName(String_ $string) : void
    {
        [$prefix, $table] = \explode('.', $string->value);
        $tableParts = \explode('/', $table);
        $pascalCaseTableParts = \array_map(function (string $token) : string {
            $tokenUnicodeString = new UnicodeString($token);
            return \ucfirst($tokenUnicodeString->camel()->toString());
        }, $tableParts);
        $table = \implode('/', $pascalCaseTableParts);
        $string->value = \sprintf('%s.%s', $prefix, $table);
    }
}
