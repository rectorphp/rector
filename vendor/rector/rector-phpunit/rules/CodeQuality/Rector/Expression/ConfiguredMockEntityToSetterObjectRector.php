<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated as experimental. It can generate broken code - it drops non-"get" config keys silently and assumes a matching setter exists. Handle it in a custom way or keep the code untouched.
 */
final class ConfiguredMockEntityToSetterObjectRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change createConfigureMock() on Entity/Document object to direct new instance with setters', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestClass
{
    public function test()
    {
        $someObject = $this->createConfiguredMock(SomeObject::class, [
            'name' => 'John',
            'surname' => 'Doe',
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestClass
{
    public function test()
    {
        $someObject = new SomeObject();
        $someObject->setName('John');
        $someObject->setSurname('Doe');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated and should not be used anymore. Remove it from your config files.', self::class));
    }
}
