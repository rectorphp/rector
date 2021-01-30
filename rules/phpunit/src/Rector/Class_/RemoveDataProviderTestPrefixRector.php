<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit\PHPUnitDataProviderTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://stackoverflow.com/a/46693675/1348344
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\RemoveDataProviderTestPrefixRectorTest
 */
final class RemoveDataProviderTestPrefixRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $providerMethodNamesToNewNames = [];

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Data provider methods cannot start with "test" prefix',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideData()
    {
        return ['123'];
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideData()
    {
        return ['123'];
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
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
        if (! $this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        $this->providerMethodNamesToNewNames = [];

        $this->renameDataProviderAnnotationsAndCollectRenamedMethods($node);
        $this->renameProviderMethods($node);

        return $node;
    }

    private function renameDataProviderAnnotationsAndCollectRenamedMethods(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

            /** @var PHPUnitDataProviderTagValueNode[] $phpunitDataProviderTagValueNodes */
            $phpunitDataProviderTagValueNodes = $phpDocInfo->findAllByType(PHPUnitDataProviderTagValueNode::class);
            if ($phpunitDataProviderTagValueNodes === []) {
                continue;
            }

            foreach ($phpunitDataProviderTagValueNodes as $dataProviderTagValueNode) {
                $oldMethodName = $dataProviderTagValueNode->getMethodName();
                if (! Strings::startsWith($oldMethodName, 'test')) {
                    continue;
                }

                $newMethodName = $this->createNewMethodName($oldMethodName);
                $dataProviderTagValueNode->changeMethodName($newMethodName);
                $phpDocInfo->markAsChanged();

                $this->providerMethodNamesToNewNames[$oldMethodName] = $newMethodName;
            }
        }
    }

    private function renameProviderMethods(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            foreach ($this->providerMethodNamesToNewNames as $oldName => $newName) {
                if (! $this->isName($classMethod, $oldName)) {
                    continue;
                }

                $classMethod->name = new Identifier($newName);
            }
        }
    }

    private function createNewMethodName(string $oldMethodName): string
    {
        $newMethodName = Strings::substring($oldMethodName, strlen('test'));

        return lcfirst($newMethodName);
    }
}
