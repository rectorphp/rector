<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use RectorPrefix202512\PHPUnit\Framework\Attributes\Depends;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddParamTypeFromDependsRector\AddParamTypeFromDependsRectorTest
 */
final class AddParamTypeFromDependsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private AttrinationFinder $attrinationFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, AttrinationFinder $attrinationFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->attrinationFinder = $attrinationFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add param type declaration based on @depends test method return type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): \stdClass
    {
        return new \stdClass();
    }

    /**
     * @depends test
     */
    public function testAnother($someObject)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): \stdClass
    {
        return new \stdClass();
    }

    /**
     * @depends test
     */
    public function testAnother(\stdClass $someObject)
    {
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if (count($classMethod->params) !== 1) {
                continue;
            }
            $soleParam = $classMethod->getParams()[0];
            // already known type
            if ($soleParam->type instanceof Node) {
                continue;
            }
            $dependsReturnType = $this->resolveReturnTypeOfDependsMethod($classMethod, $node);
            if (!$dependsReturnType instanceof Node) {
                continue;
            }
            $soleParam->type = $dependsReturnType;
            $hasChanged = \true;
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
    /**
     * @return \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name|null
     */
    private function resolveReturnTypeOfDependsMethod(ClassMethod $classMethod, Class_ $class)
    {
        $dependsMethodName = $this->resolveDependsAnnotationOrAttributeMethod($classMethod);
        if ($dependsMethodName === null || $dependsMethodName === '') {
            return null;
        }
        $dependsClassMethod = $class->getMethod($dependsMethodName);
        if (!$dependsClassMethod instanceof ClassMethod) {
            return null;
        }
        // resolve return type here
        return $dependsClassMethod->returnType;
    }
    private function resolveDependsAnnotationOrAttributeMethod(ClassMethod $classMethod): ?string
    {
        $dependsAttribute = $this->attrinationFinder->getByOne($classMethod, Depends::class);
        if ($dependsAttribute instanceof Attribute) {
            $firstArg = $dependsAttribute->args[0];
            if ($firstArg->value instanceof String_) {
                $dependsMethodName = $firstArg->value->value;
                return trim($dependsMethodName, '()');
            }
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $dependsTagValueNode = $phpDocInfo->getByName('depends');
        if (!$dependsTagValueNode instanceof PhpDocTagNode) {
            return null;
        }
        $dependsMethodName = (string) $dependsTagValueNode->value;
        return trim($dependsMethodName, '()');
    }
}
