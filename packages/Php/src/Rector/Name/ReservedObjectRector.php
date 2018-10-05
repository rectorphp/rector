<?php declare(strict_types=1);

namespace Rector\Php\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/object-typehint
 *
 * @see https://github.com/cebe/yii2/commit/9548a212ecf6e50fcdb0e5ba6daad88019cfc544
 */
final class ReservedObjectRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $reservedKeywordsToReplacements = [];

    /**
     * @param string[] $reservedKeywordsToReplacements
     */
    public function __construct(array $reservedKeywordsToReplacements)
    {
        $this->reservedKeywordsToReplacements = $reservedKeywordsToReplacements;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class Object
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SmartObject
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
        return [Identifier::class, Name::class];
    }

    /**
     * @param Identifier|Name $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identifier) {
            return $this->processIdentifier($node);
        }

        if ($node instanceof Name) {
            return $this->processName($node);
        }

        return $node;
    }

    private function processIdentifier(Identifier $identifierNode): Identifier
    {
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (strtolower($identifierNode->name) === strtolower($reservedKeyword)) {
                $identifierNode->name = $replacement;
            }
        }

        return $identifierNode;
    }

    private function processName(Name $nameNode): Name
    {
        // we look for "extends <Name>"
        $parentNode = $nameNode->getAttribute(Attribute::PARENT_NODE);
        // "Object" can part of namespace name
        if ($parentNode instanceof Namespace_) {
            return $nameNode;
        }

        // process lass part
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (strtolower($nameNode->getLast()) === strtolower($reservedKeyword)) {
                $nameNode->parts[count($nameNode->parts) - 1] = $replacement;
                $nameNode->setAttribute(Attribute::ORIGINAL_NODE, null);
            }
        }

        return $nameNode;
    }
}
