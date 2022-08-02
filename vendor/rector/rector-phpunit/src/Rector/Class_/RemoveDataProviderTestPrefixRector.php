<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/46693675/1348344
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\RemoveDataProviderTestPrefixRectorTest
 */
final class RemoveDataProviderTestPrefixRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Data provider methods cannot start with "test" prefix', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $providerMethodNamesToNewNames = $this->renameDataProviderAnnotationsAndCollectRenamedMethods($node);
        if ($providerMethodNamesToNewNames === []) {
            return null;
        }
        $this->renameProviderMethods($node, $providerMethodNamesToNewNames);
        return $node;
    }
    /**
     * @return array<string, string>
     */
    private function renameDataProviderAnnotationsAndCollectRenamedMethods(Class_ $class) : array
    {
        $oldToNewMethodNames = [];
        foreach ($class->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $dataProviderTagValueNodes = $phpDocInfo->getTagsByName('dataProvider');
            if ($dataProviderTagValueNodes === []) {
                continue;
            }
            foreach ($dataProviderTagValueNodes as $dataProviderTagValueNode) {
                if (!$dataProviderTagValueNode->value instanceof GenericTagValueNode) {
                    continue;
                }
                $oldMethodName = $dataProviderTagValueNode->value->value;
                if (\strncmp($oldMethodName, 'test', \strlen('test')) !== 0) {
                    continue;
                }
                $newMethodName = $this->createNewMethodName($oldMethodName);
                $dataProviderTagValueNode->value->value = Strings::replace($oldMethodName, '#' . \preg_quote($oldMethodName, '#') . '#', $newMethodName);
                // invoke reprint
                $dataProviderTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, null);
                $phpDocInfo->markAsChanged();
                $oldMethodNameWithoutBrackets = \rtrim($oldMethodName, '()');
                $newMethodWithoutBrackets = $this->createNewMethodName($oldMethodNameWithoutBrackets);
                $oldToNewMethodNames[$oldMethodNameWithoutBrackets] = $newMethodWithoutBrackets;
            }
        }
        return $oldToNewMethodNames;
    }
    /**
     * @param array<string, string> $oldToNewMethodsNames
     */
    private function renameProviderMethods(Class_ $class, array $oldToNewMethodsNames) : void
    {
        foreach ($class->getMethods() as $classMethod) {
            foreach ($oldToNewMethodsNames as $oldName => $newName) {
                if (!$this->isName($classMethod, $oldName)) {
                    continue;
                }
                $classMethod->name = new Identifier($newName);
            }
        }
    }
    private function createNewMethodName(string $oldMethodName) : string
    {
        $newMethodName = Strings::substring($oldMethodName, \strlen('test'));
        return \lcfirst($newMethodName);
    }
}
