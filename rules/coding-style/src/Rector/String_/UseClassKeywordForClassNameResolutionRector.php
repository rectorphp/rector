<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\UseClassKeywordForClassNameResolutionRectorTest
 */
final class UseClassKeywordForClassNameResolutionRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/Vv41Qr/1/
     */
    private const CLASS_BEFORE_STATIC_ACCESS_REGEX = '#(?<class_name>[\\\\a-zA-Z0-9_\\x80-\\xff]*)::#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use `class` keyword for class name resolution in string instead of hardcoded string reference',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$value = 'App\SomeClass::someMethod()';
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$value = \App\SomeClass . '::someMethod()';
CODE_SAMPLE
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
        return $this->nodeFactory->createConcat($exprsToConcat);
    }

    /**
     * @return string[]
     */
    public function getExistingClasses(String_ $string): array
    {
        /** @var mixed[] $matches */
        $matches = Strings::matchAll($string->value, self::CLASS_BEFORE_STATIC_ACCESS_REGEX, PREG_PATTERN_ORDER);
        if (! isset($matches['class_name'])) {
            return [];
        }

        $classNames = [];

        foreach ($matches['class_name'] as $matchedClassName) {
            if (! class_exists($matchedClassName)) {
                continue;
            }

            $classNames[] = $matchedClassName;
        }

        return $classNames;
    }

    /**
     * @param string[] $classNames
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
     * @return ClassConstFetch[]|String_[]
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
