<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\PreferThisOrSelfMethodCallRectorTest
 */
final class PreferThisOrSelfMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @deprecated
     * @var string
     */
    final public const TYPE_TO_PREFERENCE = 'type_to_preference';

    /**
     * @var array<PreferenceSelfThis>
     */
    private array $typeToPreference = [];

    public function __construct(
        private readonly AstResolver $astResolver
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes $this->... and static:: to self:: or vise versa for given types', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function run()
    {
        $this->assertEquals('a', 'a');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass extends \PHPUnit\Framework\TestCase
{
    public function run()
    {
        self::assertEquals('a', 'a');
    }
}
CODE_SAMPLE
                ,
                [
                    'PHPUnit\Framework\TestCase' => PreferenceSelfThis::PREFER_SELF(),
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typeToPreference as $type => $preference) {
            if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType($type))) {
                continue;
            }

            /** @var PreferenceSelfThis $preference */
            if ($preference->equals(PreferenceSelfThis::PREFER_SELF())) {
                return $this->processToSelf($node);
            }

            return $this->processToThis($node);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $typeToPreference = $configuration[self::TYPE_TO_PREFERENCE] ?? $configuration;
        Assert::isArray($typeToPreference);
        Assert::allString(array_keys($typeToPreference));
        Assert::allIsAOf($typeToPreference, PreferenceSelfThis::class);

        $this->typeToPreference = $typeToPreference;
    }

    private function processToSelf(MethodCall | StaticCall $node): ?StaticCall
    {
        if ($node instanceof StaticCall && ! $this->isNames(
            $node->class,
            [ObjectReference::SELF()->getValue(), ObjectReference::STATIC()->getValue()]
        )) {
            return null;
        }

        if ($node instanceof MethodCall && ! $this->isName($node->var, 'this')) {
            return null;
        }

        $classMethod = $this->astResolver->resolveClassMethodFromCall($node);
        if ($classMethod instanceof ClassMethod && ! $classMethod->isStatic()) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }

        return $this->nodeFactory->createStaticCall(ObjectReference::SELF(), $name, $node->args);
    }

    private function processToThis(MethodCall | StaticCall $node): ?MethodCall
    {
        if ($node instanceof MethodCall) {
            return null;
        }

        if (! $this->isNames(
            $node->class,
            [ObjectReference::SELF()->getValue(), ObjectReference::STATIC()->getValue()]
        )) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }

        return $this->nodeFactory->createMethodCall('this', $name, $node->args);
    }
}
