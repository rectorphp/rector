<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\UseClassKeywordForClassNameResolutionTest
 */
final class UseClassKeywordForClassNameResolutionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Use `class` keyword for class name resolution in string instead of hardcoded string reference',
            [
                new CodeSample(
                    <<<'PHP'
$value = 'App\SomeClass::someMethod()';
PHP
                    ,
                    <<<'PHP'
$value = \App\SomeClass . '::someMethod()';
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
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (substr_count($node->value, '::') !== 1) {
            return null;
        }
        // a possible static call reference
        // @see https://regex101.com/r/Vv41Qr/1/
        [$before, $possibleClass, $after] = Strings::split($node->value, '#([\\\\a-zA-Z0-9_\\x80-\\xff]*)::#');

        if (! $possibleClass || ! class_exists($possibleClass)) {
            return null;
        }

        $classConstFetch = new ClassConstFetch(new FullyQualified(ltrim($possibleClass, '\\')), 'class');

        $concat = new Concat($classConstFetch, new String_('::' . $after));
        if (! empty($before)) {
            $concat = new Concat(new String_($before), $classConstFetch);
            $concat = new Concat($concat, new String_('::' . $after));
        }

        return $concat;
    }
}
