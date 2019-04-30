<?php declare(strict_types=1);

namespace Rector\SOLID\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FinalizeClassesWithoutChildrenRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Finalize every class that has no children', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class FirstClass
{
}

class SecondClass
{
}

class ThirdClass extends SecondClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class FirstClass
{
}

class SecondClass
{
}

final class ThirdClass extends SecondClass
{
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
        if ($node->isFinal() || $node->isAbstract() || $node->isAnonymous()) {
            return null;
        }

        if ($this->isDoctrineEntity($node)) {
            return null;
        }

        /** @var string $class */
        $class = $this->getName($node);
        if ($this->parsedNodesByType->hasClassChildren($class)) {
            return null;
        }

        $node->flags |= Class_::MODIFIER_FINAL;

        return $node;
    }

    private function isDoctrineEntity(Node $node): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        return Strings::contains($node->getDocComment()->getText(), 'Entity');
    }
}
