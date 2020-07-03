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
        // @see https://regex101.com/r/Vv41Qr/1/
        $matches = Strings::matchAll($node->value, '#([\\\\a-zA-Z0-9_\\x80-\\xff]*)::#', PREG_PATTERN_ORDER);

        $classNames = array_filter($matches[1], function (string $className) {
            return class_exists($className);
        });

        if (empty($classNames)) {
            return $node;
        }

        $classNames = array_map(function (string $className) {
            return preg_quote($className);
        }, $classNames);

        // @see https://regex101.com/r/8nGS0F/1
        $splits = Strings::split($node->value, '#(' . implode('|', $classNames) . ')#');

        foreach ($splits as $split) {
            if (empty($split)) {
                continue;
            }

            if (class_exists($split)) {
                $returnNode = new ClassConstFetch(new FullyQualified(ltrim($split, '\\')), 'class');
            } else {
                $returnNode = new String_($split);
            }

            $concat = ! isset($concat) ? $returnNode : new Concat($concat, $returnNode);
        }

        if (! isset($concat)) {
            return $node;
        }

        return $concat;
    }
}
