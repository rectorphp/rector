<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
/**
 * @deprecated This rule is deprecated, as passing a service through the constructor to replace a static call is risky and this generic rule is legacy. Write a custom rule for the exact project case instead.
 */
final class StaticCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change static call to service method via constructor injection', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\Custom\SmartFileSystem;

class SomeClass
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }

    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}
CODE_SAMPLE
, [new StaticCallToMethodCall('Nette\Utils\FileSystem', 'write', 'App\Custom\SmartFileSystem', 'dumpFile')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as passing a service through the constructor to replace a static call is risky; write a custom rule for the exact project case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
