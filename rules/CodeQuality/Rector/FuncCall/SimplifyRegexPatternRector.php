<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use RectorPrefix202312\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNameResolver\Regex\RegexPatternDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/manual/en/function.preg-match.php#105924
 *
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector\SimplifyRegexPatternRectorTest
 */
final class SimplifyRegexPatternRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\Regex\RegexPatternDetector
     */
    private $regexPatternDetector;
    /**
     * @var array<string, string>
     */
    private const COMPLEX_PATTERN_TO_SIMPLE = ['[0-9]' => '\\d', '[a-zA-Z0-9_]' => '\\w', '[A-Za-z0-9_]' => '\\w', '[0-9a-zA-Z_]' => '\\w', '[0-9A-Za-z_]' => '\\w', '[\\r\\n\\t\\f\\v ]' => '\\s'];
    public function __construct(RegexPatternDetector $regexPatternDetector)
    {
        $this->regexPatternDetector = $regexPatternDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify regex pattern to known ranges', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        preg_match('#[a-zA-Z0-9+]#', $value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        preg_match('#[\w\d+]#', $value);
    }
}
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
        if (!$this->regexPatternDetector->isRegexPattern($node->value)) {
            return null;
        }
        foreach (self::COMPLEX_PATTERN_TO_SIMPLE as $complexPattern => $simple) {
            $originalValue = $node->value;
            $simplifiedValue = Strings::replace($node->value, '#' . \preg_quote($complexPattern, '#') . '#', $simple);
            if ($originalValue === $simplifiedValue) {
                continue;
            }
            $node->value = $simplifiedValue;
            return $node;
        }
        return null;
    }
}
