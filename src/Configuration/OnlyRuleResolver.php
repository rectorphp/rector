<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\Configuration\RectorRuleNameAmbiguousException;
use Rector\Exception\Configuration\RectorRuleNotFoundException;
use ReflectionClass;
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
    public function resolve(string $rule): string
    {
        // fix wrongly double escaped backslashes
        $rule = str_replace('\\\\', '\\', $rule);
        // remove single quotes appearing when single-quoting arguments on windows
        if (strncmp($rule, "'", strlen("'")) === 0 && substr_compare($rule, "'", -strlen("'")) === 0) {
            $rule = (string) substr($rule, 1, -1);
        }
        $rule = ltrim($rule, '\\');
        foreach ($this->rectors as $rector) {
            if (get_class($rector) === $rule) {
                return $rule;
            }
        }
        // allow short rule names if there are not duplicates
        $matching = [];
        foreach ($this->rectors as $rector) {
            if (substr_compare(get_class($rector), '\\' . $rule, -strlen('\\' . $rule)) === 0) {
                $matching[] = get_class($rector);
            }
        }
        $matching = array_unique($matching);
        if (count($matching) === 1) {
            return $matching[0];
        }
        if (count($matching) > 1) {
            sort($matching);
            $message = sprintf('Short rule name "%s" is ambiguous. Specify the full rule name:' . \PHP_EOL . '- ' . implode(\PHP_EOL . '- ', $matching), $rule);
            throw new RectorRuleNameAmbiguousException($message);
        }
        if (strpos($rule, '\\') === \false) {
            // the shell has eaten unescaped backslashes, e.g. --only=\Rector\Some\Rule
            $flattenMatching = [];
            foreach ($this->rectors as $rector) {
                if (str_replace('\\', '', get_class($rector)) === $rule) {
                    $flattenMatching[] = get_class($rector);
                }
            }
            $flattenMatching = array_unique($flattenMatching);
            if (count($flattenMatching) === 1) {
                return $flattenMatching[0];
            }
            $message = sprintf('Rule "%s" was not found.%sThe rule has no namespace. Make sure to escape the backslashes, and add quotes around the rule name: --only="My\Rector\Rule"', $rule, \PHP_EOL);
        } else {
            // the rule class exists, it is just missing in the config
            if ($this->isRectorRuleClass($rule)) {
                throw new RectorRuleNotFoundException($this->createUnregisteredMessage($rule));
            }
            $message = sprintf('Rule "%s" was not found.%sMake sure it is registered in your config or in one of the sets', $rule, \PHP_EOL);
        }
        throw new RectorRuleNotFoundException($message);
    }
    /**
     * Is this an existing rule class, that is just not registered in the config?
     */
    private function isRectorRuleClass(string $className): bool
    {
        if (!class_exists($className)) {
            return \false;
        }
        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->isAbstract()) {
            return \false;
        }
        return $reflectionClass->implementsInterface(RectorInterface::class);
    }
    private function createUnregisteredMessage(string $ruleClass): string
    {
        $shortRuleClass = (string) substr((string) strrchr($ruleClass, '\\'), 1);
        return sprintf('Rule "%s" exists, but is not registered in your Rector config.%sRegister it in your rector.php:' . \PHP_EOL . \PHP_EOL . '    ->withRules([%s::class])', $ruleClass, \PHP_EOL, $shortRuleClass);
    }
}
