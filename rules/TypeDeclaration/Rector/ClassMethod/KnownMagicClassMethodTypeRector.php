<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\KnownMagicClassMethodTypeRector\KnownMagicClassMethodTypeRectorTest
 *
 * @see https://www.php.net/manual/en/language.oop5.overloading.php#object.call
 */
final class KnownMagicClassMethodTypeRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add known magic methods parameter and return type declarations', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function __call($method, $args)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __call(string $method, array $args)
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
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isMagic()) {
                continue;
            }
            if ($this->isName($classMethod, MethodName::CALL)) {
                $firstParam = $classMethod->getParams()[0];
                if (!$firstParam->type instanceof Node) {
                    $firstParam->type = new Identifier('string');
                    $hasChanged = \true;
                }
                $secondParam = $classMethod->getParams()[1];
                if (!$secondParam->type instanceof Node) {
                    $secondParam->type = new Name('array');
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
