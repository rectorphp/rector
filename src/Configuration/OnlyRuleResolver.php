<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\Configuration\RectorRuleNameAmbiguousException;
use Rector\Exception\Configuration\RectorRuleNotFoundException;
/**
 * @see \Rector\Tests\Configuration\OnlyRuleResolverTest
 */
final class OnlyRuleResolver
{
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private array $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors)
    {
        $this->rectors = $rectors;
    }
    public function resolve(string $rule) : string
    {
        //fix wrongly double escaped backslashes
        $rule = \str_replace('\\\\', '\\', $rule);
        //remove single quotes appearing when single-quoting arguments on windows
        if (\strncmp($rule, "'", \strlen("'")) === 0 && \substr_compare($rule, "'", -\strlen("'")) === 0) {
            $rule = \substr($rule, 1, -1);
        }
        $rule = \ltrim($rule, '\\');
        foreach ($this->rectors as $rector) {
            if (\get_class($rector) === $rule) {
                return $rule;
            }
        }
        //allow short rule names if there are not duplicates
        $matching = [];
        foreach ($this->rectors as $rector) {
            if (\substr_compare(\get_class($rector), '\\' . $rule, -\strlen('\\' . $rule)) === 0) {
                $matching[] = \get_class($rector);
            }
        }
        $matching = \array_unique($matching);
        if (\count($matching) == 1) {
            return $matching[0];
        }
        if (\count($matching) > 1) {
            \sort($matching);
            $message = \sprintf('Short rule name "%s" is ambiguous. Specify the full rule name:' . \PHP_EOL . '- ' . \implode(\PHP_EOL . '- ', $matching), $rule);
            throw new RectorRuleNameAmbiguousException($message);
        }
        if (\strpos($rule, '\\') === \false) {
            $message = \sprintf('Rule "%s" was not found.%sThe rule has no namespace. Make sure to escape the backslashes, and add quotes around the rule name: --only="My\\Rector\\Rule"', $rule, \PHP_EOL);
        } else {
            $message = \sprintf('Rule "%s" was not found.%sMake sure it is registered in your config or in one of the sets', $rule, \PHP_EOL);
        }
        throw new RectorRuleNotFoundException($message);
    }
}
