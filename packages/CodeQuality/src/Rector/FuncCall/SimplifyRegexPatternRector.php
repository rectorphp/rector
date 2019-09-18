<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Php\Regex\RegexPatternArgumentManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see http://php.net/manual/en/function.preg-match.php#105924
 *
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyRegexPatternRector\SimplifyRegexPatternRectorTest
 */
final class SimplifyRegexPatternRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $complexPatternToSimple = [
        '[0-9]' => '\d',
        '[a-zA-Z0-9_]' => '\w',
        '[A-Za-z0-9_]' => '\w',
        '[0-9a-zA-Z_]' => '\w',
        '[0-9A-Za-z_]' => '\w',
        '[\r\n\t\f\v ]' => '\s',
    ];

    /**
     * @var RegexPatternArgumentManipulator
     */
    private $regexPatternArgumentManipulator;

    public function __construct(RegexPatternArgumentManipulator $regexPatternArgumentManipulator)
    {
        $this->regexPatternArgumentManipulator = $regexPatternArgumentManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify regex pattern to known ranges', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        preg_match('#[a-zA-Z0-9+]#', $value);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        preg_match('#[\w\d+]#', $value);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $patterns = $this->regexPatternArgumentManipulator->matchCallArgumentWithRegexPattern($node);

        foreach ($patterns as $pattern) {
            foreach ($this->complexPatternToSimple as $complexPattern => $simple) {
                $pattern->value = Strings::replace(
                    $pattern->value,
                    '#' . preg_quote($complexPattern, '#') . '#',
                    $simple
                );
            }
        }

        return $node;
    }
}
