<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\IssetOnPropertyObjectToPropertyExistsRectorTest
 * @see https://3v4l.org/TI8XL Only usable on single property value and property has value
 */
final class IssetOnPropertyObjectToPropertyExistsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change isset on property object to property_exists()',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    private $x;

    public function run(): void
    {
        isset($this->x);
    }
}
PHP
,
                    <<<'PHP'
class SomeClass
{
    private $x;

    public function run(): void
    {
        property_exists('SomeClass', 'x');
    }
}
PHP
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Isset_::class];
    }

    /**
     * @param Isset_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->vars as $issetVar) {
            if (! $issetVar instanceof PropertyFetch) {
                continue;
            }

            $className = $issetVar->var->getAttribute('className');
            /** @var Identifier $name */
            $name = $issetVar->name;
            $property = $name->toString();

            return new FuncCall(
                new Name('property_exists'),
                [new Arg(new String_($className)), new Arg(new String_($property))]
            );
        }

        return null;
    }
}
