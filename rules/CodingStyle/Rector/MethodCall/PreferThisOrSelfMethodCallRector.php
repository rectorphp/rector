<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
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
     * @var string
     */
    public const TYPE_TO_PREFERENCE = 'type_to_preference';

    /**
     * @var string
     */
    private const SELF = 'self';

    /**
     * @var array<PreferenceSelfThis>
     */
    private array $typeToPreference = [];

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
                    self::TYPE_TO_PREFERENCE => [
                        'PHPUnit\Framework\TestCase' => PreferenceSelfThis::PREFER_SELF(),
                    ],
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
     * @param array<string, PreferenceSelfThis[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $typeToPreference = $configuration[self::TYPE_TO_PREFERENCE] ?? [];
        Assert::allIsAOf($typeToPreference, PreferenceSelfThis::class);

        $this->typeToPreference = $typeToPreference;
    }

    private function processToSelf(MethodCall | StaticCall $node): ?StaticCall
    {
        if ($node instanceof StaticCall && ! $this->isNames($node->class, [self::SELF, 'static'])) {
            return null;
        }

        if ($node instanceof MethodCall && ! $this->isName($node->var, 'this')) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }

        return $this->nodeFactory->createStaticCall(self::SELF, $name, $node->args);
    }

    private function processToThis(MethodCall | StaticCall $node): ?MethodCall
    {
        if ($node instanceof MethodCall) {
            return null;
        }

        if (! $this->isNames($node->class, [self::SELF, 'static'])) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }

        return $this->nodeFactory->createMethodCall('this', $name, $node->args);
    }
}
