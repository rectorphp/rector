<?php

declare(strict_types=1);

namespace Rector\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\MethodBody\ReturnThisRemoveRector\ReturnThisRemoveRectorTest
 */
final class ReturnThisRemoveRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classesToDefluent = [];

    /**
     * @param string[] $classesToDefluent
     */
    public function __construct(array $classesToDefluent = [])
    {
        $this->classesToDefluent = $classesToDefluent;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes "return $this;" from *fluent interfaces* for specified classes.', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function someFunction()
    {
    }

    public function otherFunction()
    {
    }
}
PHP
                ,
                [['SomeExampleClass']]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof Variable) {
            return null;
        }

        if (! $this->isName($node->expr, 'this')) {
            return null;
        }

        if (! $this->isObjectTypes($node->expr, $this->classesToDefluent)) {
            return null;
        }

        $this->removeNode($node);

        $methodNode = $node->getAttribute(AttributeKey::METHOD_NODE);
        if ($methodNode === null) {
            throw new ShouldNotHappenException();
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $methodNode->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->removeByType(ReturnTagValueNode::class);

        return null;
    }
}
