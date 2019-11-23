<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\PreferThisOrSelfMethodCallRectorTest
 */
final class PreferThisOrSelfMethodCallRector extends AbstractRector
{
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

    /**
     * @param string[] $typeToPreference
     */
    public function __construct(array $typeToPreference = [])
    {
        $this->typeToPreference = $typeToPreference;
    }

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
                ['PHPUnit\TestCase' => self::PREFER_SELF]
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

    private function ensurePreferenceIsValid($preference): void
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
