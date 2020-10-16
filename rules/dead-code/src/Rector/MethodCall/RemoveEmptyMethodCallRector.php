<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\MethodCall;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use PHPStan\Node\Method\MethodCall as PHPStanMethodCall;
use PhpParser\Parser;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

/**
 * @see \Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends AbstractRector
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty method call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new Some();
$some->callThis();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new Some();
$some->callThis();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $type = $node->var
                     ->getAttribute(AttributeKey::SCOPE)
                     ->getType($node->var);

        $fileClass = $type->getClassReflection()
                          ->getFileName();
        $className = $type->getClassName();

        $contentNodes = $this->parser->parse(FileSystem::read($fileClass));
        $class = $this->betterNodeFinder->findFirstInstanceOf($contentNodes, Class_::class);

        $classMethod = $class->getMethod((string) $node->name);
        $isNonEmpty = $this->betterNodeFinder->find($classMethod->stmts, function ($node): bool {
            return ! $node instanceof Nop;
        });

        if ($isNonEmpty !== []) {
            return null;
        }

        $this->removeNode($node);
        return $node;
    }
}
