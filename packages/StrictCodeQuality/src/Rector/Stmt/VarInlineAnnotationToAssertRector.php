<?php declare(strict_types=1);

namespace Rector\StrictCodeQuality\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector\VarInlineAnnotationToAssertRectorTest
 */
final class VarInlineAnnotationToAssertRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turn @var inline checks above code to assert() of hte type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        $value->call();
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        /** @var SpecificClass $value */
        assert($value instanceof SpecificClass);
        $value->call();
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Stmt::class];
    }

    /**
     * @param Stmt $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->getPhpDocInfo($node);
        if ($phpDocInfo === null) {
            return null;
        }

        $variable = $this->betterNodeFinder->findFirstInstanceOf($node, Variable::class);
        if (! $variable instanceof Variable) {
            return null;
        }

        $variable->setAttribute('comments', []);
        $type = $phpDocInfo->getVarType();

        $assertFuncCall = null;
        if ($type instanceof ObjectType) {
            $instanceOf = new Instanceof_($variable, new FullyQualified($type->getClassName()));
            $assertFuncCall = $this->createFunction('assert', [$instanceOf]);
        }

        if ($type instanceof IntegerType) {
            $isInt = $this->createFunction('is_int', [$variable]);
            $assertFuncCall = $this->createFunction('assert', [$isInt]);
        }

        if ($type instanceof StringType) {
            $isInt = $this->createFunction('is_string', [$variable]);
            $assertFuncCall = $this->createFunction('assert', [$isInt]);
        }

        if ($type instanceof BooleanType) {
            $isInt = $this->createFunction('is_bool', [$variable]);
            $assertFuncCall = $this->createFunction('assert', [$isInt]);
        }

        if ($assertFuncCall === null) {
            return null;
        }

        $this->addNodeBeforeNode($assertFuncCall, $node);

        // cleanup @var annotation
        $varTagValueNode = $phpDocInfo->getByType(VarTagValueNode::class);
        $phpDocInfo->removeTagValueNodeFromNode($varTagValueNode);

        $this->docBlockManipulator->updateNodeWithPhpDocInfo($node, $phpDocInfo);

        return $node;
    }
}
