<?php

declare (strict_types=1);
namespace Rector\Symfony\Yaml\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\Yaml\Rector\StaticCall\YamlFlagsToConstantRector\YamlFlagsToConstantRectorTest
 */
final class YamlFlagsToConstantRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var array<string, array{position: int, transforms: array<array{before: bool|array<bool>, after: string|int}>}>
     */
    private const METHODS_TO_FLAGS = ['parse' => ['position' => 1, 'transforms' => [['before' => [\false, \false, \true], 'after' => 'PARSE_OBJECT_FOR_MAP'], ['before' => [\false, \true], 'after' => 'PARSE_OBJECT'], ['before' => \true, 'after' => 'PARSE_EXCEPTION_ON_INVALID_TYPE'], ['before' => \false, 'after' => 0]]], 'dump' => ['position' => 3, 'transforms' => [['before' => [\false, \true], 'after' => 'DUMP_OBJECT'], ['before' => \true, 'after' => 'DUMP_EXCEPTION_ON_INVALID_TYPE']]]];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/yaml', '>=3.1');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace the boolean flag arguments of Yaml::parse() and Yaml::dump() with the matching Yaml flag constants', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Yaml\Yaml;

$data = Yaml::parse($yaml, false, false, true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Yaml\Yaml;

$data = Yaml::parse($yaml, Yaml::PARSE_OBJECT_FOR_MAP);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?StaticCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->class, SymfonyClass::YAML)) {
            return null;
        }
        $methodName = $this->getName($node->name);
        if ($methodName === null || !isset(self::METHODS_TO_FLAGS[$methodName])) {
            return null;
        }
        $definition = self::METHODS_TO_FLAGS[$methodName];
        $position = $definition['position'];
        foreach ($definition['transforms'] as $transform) {
            if ($this->applyTransform($node, $position, $transform['before'], $transform['after'])) {
                return $node;
            }
        }
        return null;
    }
    /**
     * @param bool|array<bool> $before
     * @param int|string $after
     */
    private function applyTransform(StaticCall $staticCall, int $position, $before, $after): bool
    {
        $args = $staticCall->getArgs();
        if (is_array($before)) {
            $collectedValues = $this->collectArgValues($args, $position, count($before));
            if ($collectedValues !== $before) {
                return \false;
            }
            $args[$position]->value = $this->createAfterExpr($after);
            // collapsed flags: drop the now redundant trailing boolean arguments
            for ($i = $position + 1; $i < $position + count($before); ++$i) {
                unset($staticCall->args[$i]);
            }
            return \true;
        }
        $flagArg = $args[$position] ?? null;
        if (!$flagArg instanceof Arg || $flagArg->name instanceof Identifier) {
            return \false;
        }
        if (!$this->valueResolver->isValue($flagArg->value, $before)) {
            return \false;
        }
        $flagArg->value = $this->createAfterExpr($after);
        return \true;
    }
    /**
     * @param array<int, Arg> $args
     * @return array<bool>|null
     */
    private function collectArgValues(array $args, int $position, int $length): ?array
    {
        $values = [];
        for ($i = 0; $i < $length; ++$i) {
            $arg = $args[$position + $i] ?? null;
            if (!$arg instanceof Arg || $arg->name instanceof Identifier) {
                return null;
            }
            $value = $this->valueResolver->getValue($arg->value);
            if (!is_bool($value)) {
                return null;
            }
            $values[] = $value;
        }
        return $values;
    }
    /**
     * @param int|string $after
     */
    private function createAfterExpr($after): Expr
    {
        if (is_int($after)) {
            return new Int_($after);
        }
        return $this->nodeFactory->createClassConstFetch(SymfonyClass::YAML, $after);
    }
}
