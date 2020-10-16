<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Parser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
        /** @var Scope $scope */
        $scope = $node->var->getAttribute(AttributeKey::SCOPE);
        /** @var ObjectType $type */
        $type = $scope->getType($node->var);
        /** @var ClassReflection|null $classReflection */
        $classReflection = $type->getClassReflection();

        if ($classReflection === null) {
            return null;
        }

        /** @var string $fileClass */
        $fileClass = $classReflection->getFileName();
        $className = $type->getClassName();

        /** @var Node[] $contentNodes */
        $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileClass));
        $classes = $this->betterNodeFinder->findInstanceOf($contentNodes, Class_::class);

        if ($classes === []) {
            return null;
        }

        foreach ($classes as $class) {
            $shortClassName = $class->name;
            if (Strings::endsWith(
                $className,
                '\\' . (string) $shortClassName
            ) || $className === (string) $shortClassName) {
                break;
            }
        }

        /** @var Identifier $methodIdentifier */
        $methodIdentifier = $node->name;
        /** @var ClassMethod $classMethod */
        $classMethod = $class->getMethod((string) $methodIdentifier);

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
