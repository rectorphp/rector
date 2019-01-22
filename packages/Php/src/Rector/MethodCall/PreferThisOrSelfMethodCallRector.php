<?php declare(strict_types=1);

namespace Rector\Php\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use function Safe\sprintf;

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
                <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\TestCase
{
    public function run()
    {
        $this->assertThis();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\TestCase
{
    public function run()
    {
        self::assertThis();
    }
}
CODE_SAMPLE
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
            if (! $this->isType($node, $type)) {
                continue;
            }

            $this->ensurePreferceIsValid($preference);

            if ($preference === self::PREFER_SELF) {
                return $this->processToSelf($node);
            }

            if ($preference === self::PREFER_THIS) {
                return $this->processToThis($node);
            }
        }

        return $node;
    }

    /**
     * @param mixed $preference
     */
    private function ensurePreferceIsValid($preference): void
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

        return new StaticCall(new Name('self'), $node->name);
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

        return new MethodCall(new Variable('this'), $node->name);
    }
}
