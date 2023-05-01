<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\String_;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\UseClassKeywordForClassNameResolutionRectorTest
 */
final class UseClassKeywordForClassNameResolutionRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/Vv41Qr/1/
     */
    private const CLASS_BEFORE_STATIC_ACCESS_REGEX = '#(?<class_name>[\\\\a-zA-Z0-9_\\x80-\\xff]*)::#';
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use `class` keyword for class name resolution in string instead of hardcoded string reference', [new CodeSample(<<<'CODE_SAMPLE'
$value = 'App\SomeClass::someMethod()';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = \App\SomeClass::class . '::someMethod()';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stringKind = $node->getAttribute(AttributeKey::KIND);
        if (\in_array($stringKind, [String_::KIND_HEREDOC, String_::KIND_NOWDOC], \true)) {
            return null;
        }
        $classNames = $this->getExistingClasses($node);
        $classNames = $this->filterOurShortClasses($classNames);
        if ($classNames === []) {
            return null;
        }
        $parts = $this->getParts($node, $classNames);
        if ($parts === []) {
            return null;
        }
        $exprsToConcat = $this->createExpressionsToConcat($parts);
        return $this->nodeFactory->createConcat($exprsToConcat);
    }
    /**
     * @param string[] $classNames
     * @return mixed[]
     */
    private function getParts(String_ $string, array $classNames) : array
    {
        $quotedClassNames = \array_map('preg_quote', $classNames);
        // @see https://regex101.com/r/8nGS0F/1
        $parts = Strings::split($string->value, '#(' . \implode('|', $quotedClassNames) . ')#');
        return \array_filter($parts, static function (string $className) : bool {
            return $className !== '';
        });
    }
    /**
     * @return string[]
     */
    private function getExistingClasses(String_ $string) : array
    {
        /** @var mixed[] $matches */
        $matches = Strings::matchAll($string->value, self::CLASS_BEFORE_STATIC_ACCESS_REGEX, \PREG_PATTERN_ORDER);
        if (!isset($matches['class_name'])) {
            return [];
        }
        $classNames = [];
        foreach ($matches['class_name'] as $matchedClassName) {
            if (!$this->reflectionProvider->hasClass($matchedClassName)) {
                continue;
            }
            $classNames[] = $matchedClassName;
        }
        return $classNames;
    }
    /**
     * @param string[] $parts
     * @return ClassConstFetch[]|String_[]
     */
    private function createExpressionsToConcat(array $parts) : array
    {
        $exprsToConcat = [];
        foreach ($parts as $part) {
            if ($this->reflectionProvider->hasClass($part)) {
                $exprsToConcat[] = new ClassConstFetch(new FullyQualified(\ltrim($part, '\\')), 'class');
            } else {
                $exprsToConcat[] = new String_($part);
            }
        }
        return $exprsToConcat;
    }
    /**
     * @param string[] $classNames
     * @return string[]
     */
    private function filterOurShortClasses(array $classNames) : array
    {
        return \array_filter($classNames, static function (string $className) : bool {
            return \strpos($className, '\\') !== \false;
        });
    }
}
