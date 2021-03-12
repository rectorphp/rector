<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\NodeFactory\ConfigFileFactory;
use Rector\Transform\NodeFactory\ProvideConfigFilePathClassMethodFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Transform\Tests\Rector\Class_\CommunityTestCaseRector\CommunityTestCaseRectorTest
 */
final class CommunityTestCaseRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ABSTRACT_COMMUNITY_TEST_CLASS = 'Rector\Testing\PHPUnit\AbstractCommunityRectorTestCase';

    /**
     * @var ProvideConfigFilePathClassMethodFactory
     */
    private $provideConfigFilePathClassMethodFactory;

    /**
     * @var ConfigFileFactory
     */
    private $configFileFactory;

    public function __construct(
        ProvideConfigFilePathClassMethodFactory $provideConfigFilePathClassMethodFactory,
        ConfigFileFactory $configFileFactory
    ) {
        $this->provideConfigFilePathClassMethodFactory = $provideConfigFilePathClassMethodFactory;
        $this->configFileFactory = $configFileFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Rector test case to Community version', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SomeClassTest extends AbstractRectorTestCase
{
    public function getRectorClass(): string
    {
        return SomeRector::class;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Rector\Testing\PHPUnit\AbstractCommunityRectorTestCase;

final class SomeClassTest extends AbstractCommunityRectorTestCase
{
    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
CODE_SAMPLE

            ),
        ]);
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
        if ($node->extends === null) {
            return null;
        }

        if (! $this->isNames(
            $node->extends,
            [self::ABSTRACT_COMMUNITY_TEST_CLASS, 'Rector\Testing\PHPUnit\AbstractRectorTestCase']
        )) {
            return null;
        }

        $getRectorClassMethod = $node->getMethod('getRectorClass');
        if (! $getRectorClassMethod instanceof ClassMethod) {
            return null;
        }

        $node->extends = new FullyQualified(self::ABSTRACT_COMMUNITY_TEST_CLASS);

        $this->removeNode($getRectorClassMethod);
        $node->stmts[] = $this->provideConfigFilePathClassMethodFactory->create();

        $this->configFileFactory->createConfigFile($getRectorClassMethod);

        return $node;
    }
}
