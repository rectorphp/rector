<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\AddSeeTestAnnotationRectorTest
 */
final class AddSeeTestAnnotationRector extends AbstractRector
{
    /**
     * @var TestClassResolver
     */
    private $testClassResolver;

    public function __construct(TestClassResolver $testClassResolver)
    {
        $this->testClassResolver = $testClassResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * @see \SomeServiceTest
 */
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
CODE_SAMPLE
                ),
            ]
        );
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
        $testCaseClassName = $this->testClassResolver->resolveFromClass($node);
        if ($testCaseClassName === null) {
            return null;
        }

        if ($this->shouldSkipClass($node, $testCaseClassName)) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $seeTagNode = $this->createSeePhpDocTagNode($testCaseClassName);
        $phpDocInfo->addPhpDocTagNode($seeTagNode);

        $this->notifyNodeFileInfo($node);

        return $node;
    }

    private function shouldSkipClass(Class_ $class, string $testCaseClassName): bool
    {
        // we are in the test case
        if ($this->isName($class, '*Test')) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $class->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return true;
        }

        $seeTags = $phpDocInfo->getTagsByName('see');

        // is the @see annotation already added
        foreach ($seeTags as $seeTag) {
            if (! $seeTag->value instanceof GenericTagValueNode) {
                continue;
            }

            $seeTagClass = ltrim($seeTag->value->value, '\\');
            if ($seeTagClass === $testCaseClassName) {
                return true;
            }
        }

        return false;
    }

    private function createSeePhpDocTagNode(string $className): PhpDocTagNode
    {
        return new AttributeAwarePhpDocTagNode('@see', new AttributeAwareGenericTagValueNode('\\' . $className));
    }
}
