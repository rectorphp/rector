<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Iterator;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @see https://3v4l.org/5PJid
 *
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\ReturnArrayClassMethodToYieldRectorTest
 */
final class ReturnArrayClassMethodToYieldRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $methodsByType = [];

    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    /**
     * @var PhpDocInfo|null
     */
    private $returnPhpDocInfo;

    /**
     * @var Comment[]
     */
    private $returnComments = [];

    /**
     * @param string[][] $methodsByType
     */
    public function __construct(NodeTransformer $nodeTransformer, array $methodsByType = [])
    {
        $this->nodeTransformer = $nodeTransformer;
        $this->methodsByType = $methodsByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns array return to yield return in specific type and method', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}
PHP
                ,
                <<<'PHP'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield 'event' => 'callback';
    }
}
PHP
                ,
                [
                    'EventSubscriberInterface' => ['getSubscribedEvents'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodsByType as $type => $methods) {
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($methods as $methodName) {
                if (! $this->isName($node, $methodName)) {
                    continue;
                }

                $arrayNode = $this->collectReturnArrayNodesFromClassMethod($node);
                if ($arrayNode === null) {
                    continue;
                }

                $this->transformArrayToYieldsOnMethodNode($node, $arrayNode);

                $this->completeComments($node);
            }
        }

        return $node;
    }

    private function collectReturnArrayNodesFromClassMethod(ClassMethod $classMethod): ?Array_
    {
        if ($classMethod->stmts === null) {
            return null;
        }

        foreach ($classMethod->stmts as $statement) {
            if ($statement instanceof Return_) {
                if (! $statement->expr instanceof Array_) {
                    continue;
                }

                $this->returnPhpDocInfo = $statement->getAttribute(AttributeKey::PHP_DOC_INFO);
                $this->returnComments = $statement->getComments();

                return $statement->expr;
            }
        }

        return null;
    }

    private function transformArrayToYieldsOnMethodNode(ClassMethod $classMethod, Array_ $arrayNode): void
    {
        $yieldNodes = $this->nodeTransformer->transformArrayToYields($arrayNode);

        // remove whole return node
        $parentNode = $arrayNode->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            throw new ShouldNotHappenException();
        }

        $this->removeNode($parentNode);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->removeByType(ReturnTagValueNode::class);

        // change return typehint
        $classMethod->returnType = new FullyQualified(Iterator::class);

        $classMethod->stmts = array_merge((array) $classMethod->stmts, $yieldNodes);
    }

    private function completeComments(ClassMethod $classMethod): void
    {
        if ($this->returnPhpDocInfo === null && $this->returnComments === []) {
            return;
        }

        $classMethod->setAttribute(AttributeKey::PHP_DOC_INFO, $this->returnPhpDocInfo);
        $classMethod->setAttribute('comments', $this->returnComments);
    }
}
