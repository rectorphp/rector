<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @changelog https://wiki.php.net/rfc/object-typehint https://github.com/cebe/yii2/commit/9548a212ecf6e50fcdb0e5ba6daad88019cfc544
 *
 * @see \Rector\Tests\Php71\Rector\Name\ReservedObjectRector\ReservedObjectRectorTest
 */
final class ReservedObjectRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface, \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const RESERVED_KEYWORDS_TO_REPLACEMENTS = 'reserved_keywords_to_replacements';
    /**
     * @var array<string, string>
     */
    private $reservedKeywordsToReplacements = [];
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::RESERVED_OBJECT_KEYWORD;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class Object
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SmartObject
{
}
CODE_SAMPLE
, ['ReservedObject' => 'SmartObject', 'Object' => 'AnotherSmartObject'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Identifier::class, \PhpParser\Node\Name::class];
    }
    /**
     * @param Identifier|Name $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Identifier) {
            return $this->processIdentifier($node);
        }
        return $this->processName($node);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $reservedKeywordsToReplacements = $configuration[self::RESERVED_KEYWORDS_TO_REPLACEMENTS] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::isArray($reservedKeywordsToReplacements);
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString(\array_keys($reservedKeywordsToReplacements));
        \RectorPrefix20220209\Webmozart\Assert\Assert::allString($reservedKeywordsToReplacements);
        $this->reservedKeywordsToReplacements = $reservedKeywordsToReplacements;
    }
    private function processIdentifier(\PhpParser\Node\Identifier $identifier) : \PhpParser\Node\Identifier
    {
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (!$this->isName($identifier, $reservedKeyword)) {
                continue;
            }
            $identifier->name = $replacement;
            return $identifier;
        }
        return $identifier;
    }
    private function processName(\PhpParser\Node\Name $name) : \PhpParser\Node\Name
    {
        // we look for "extends <Name>"
        $parentNode = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // "Object" can part of namespace name
        if ($parentNode instanceof \PhpParser\Node\Stmt\Namespace_) {
            return $name;
        }
        // process lass part
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (\strtolower($name->getLast()) === \strtolower($reservedKeyword)) {
                $name->parts[\count($name->parts) - 1] = $replacement;
                // invoke override
                $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            }
        }
        return $name;
    }
}
