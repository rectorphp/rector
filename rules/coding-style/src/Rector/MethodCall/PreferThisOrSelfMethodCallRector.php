<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\PreferThisOrSelfMethodCallRectorTest
 */
final class PreferThisOrSelfMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const TYPE_TO_PREFERENCE = '$typeToPreference';

    /**
     * @var string
     */
    private const PREFER_SELF = 'self';

    /**
     * @var string
     */
    private const PREFER_THIS = 'this';

    /**
     * @var string[]
     */
    private $typeToPreference = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes $this->... to self:: or vise versa for specific types', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass extends PHPUnit\TestCase
{
    public function run()
    {
        $this->assertThis();
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass extends PHPUnit\TestCase
{
    public function run()
    {
        self::assertThis();
    }
}
PHP
                ,
                [
                    self::TYPE_TO_PREFERENCE => [
                        'PHPUnit\TestCase' => self::PREFER_SELF,
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            $this->ensurePreferenceIsValid($preference);

            if ($preference === self::PREFER_SELF) {
                return $this->processToSelf($node);
            }

            if ($preference === self::PREFER_THIS) {
                return $this->processToThis($node);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->typeToPreference = $configuration[self::TYPE_TO_PREFERENCE] ?? [];
    }

    private function ensurePreferenceIsValid(string $preference): void
    {
        $allowedPreferences = [self::PREFER_THIS, self::PREFER_SELF];
        if (in_array($preference, $allowedPreferences, true)) {
            return;
        }

        throw new InvalidRectorConfigurationException(sprintf(
            'Preference configuration "%s" for "%s" is not valid. Use one of "%s"',
            $preference,
            self::class,
            implode('", "', $allowedPreferences)
        ));
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processToSelf(Node $node): ?StaticCall
    {
        if ($node instanceof StaticCall) {
            return null;
        }

        if (! $this->isName($node->var, 'this')) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }

        return $this->createStaticCall('self', $name, $node->args);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function processToThis(Node $node): ?MethodCall
    {
        if ($node instanceof MethodCall) {
            return null;
        }

        if (! $node->class instanceof Name) {
            return null;
        }

        if (! $this->isName($node->class, 'self')) {
            return null;
        }

        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }

        return $this->createMethodCall('this', $name, $node->args);
    }
}
