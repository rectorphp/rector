<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\UseClassKeywordForClassNameResolutionRectorTest
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
        $classNames = $this->getExistingClasses($node);
        if ($classNames === []) {
            return $node;
        }

        $parts = $this->getParts($node, $classNames);
        if ($parts === []) {
            return null;
        }

        $exprsToConcat = $this->createExpressionsToConcat($parts);
        return $this->createConcat($exprsToConcat);
    }

    /**
     * @return mixed[]
     */
    public function getExistingClasses(String_ $string): array
    {
        // @see https://regex101.com/r/Vv41Qr/1/
        $matches = Strings::matchAll($string->value, '#([\\\\a-zA-Z0-9_\\x80-\\xff]*)::#', PREG_PATTERN_ORDER);

        return array_filter($matches[1], function (string $className): bool {
            return class_exists($className);
        });
    }

    /**
     * @return mixed[]
     */
    public function getParts(String_ $string, array $classNames): array
    {
        $classNames = array_map(function (string $className): string {
            return preg_quote($className);
        }, $classNames);

        // @see https://regex101.com/r/8nGS0F/1
        $parts = Strings::split($string->value, '#(' . implode('|', $classNames) . ')#');

        return array_filter($parts, function (string $className): bool {
            return $className !== '';
        });
    }

    /**
     * @param string[] $parts
     * @return ClassConstFetch[]|\PhpParser\Node\Scalar\String_[]
     */
    private function createExpressionsToConcat(array $parts): array
    {
        $exprsToConcat = [];
        foreach ($parts as $part) {
            if (class_exists($part)) {
                $exprsToConcat[] = new ClassConstFetch(new FullyQualified(ltrim($part, '\\')), 'class');
            } else {
                $exprsToConcat[] = new String_($part);
            }
        }
        return $exprsToConcat;
    }
}
