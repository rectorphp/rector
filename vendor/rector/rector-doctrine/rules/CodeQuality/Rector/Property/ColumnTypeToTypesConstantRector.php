<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\Enum\MappingClass;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\ColumnTypeToTypesConstantRector\ColumnTypeToTypesConstantRectorTest
 */
final class ColumnTypeToTypesConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    private const TYPES_CLASS = 'Doctrine\DBAL\Types\Types';
    /**
     * @var array<string, string>
     */
    private const TYPE_TO_CONSTANT = ['array' => 'ARRAY', 'ascii_string' => 'ASCII_STRING', 'bigint' => 'BIGINT', 'binary' => 'BINARY', 'blob' => 'BLOB', 'boolean' => 'BOOLEAN', 'date' => 'DATE_MUTABLE', 'date_immutable' => 'DATE_IMMUTABLE', 'dateinterval' => 'DATEINTERVAL', 'datetime' => 'DATETIME_MUTABLE', 'datetime_immutable' => 'DATETIME_IMMUTABLE', 'datetimetz' => 'DATETIMETZ_MUTABLE', 'datetimetz_immutable' => 'DATETIMETZ_IMMUTABLE', 'decimal' => 'DECIMAL', 'float' => 'FLOAT', 'guid' => 'GUID', 'integer' => 'INTEGER', 'json' => 'JSON', 'object' => 'OBJECT', 'simple_array' => 'SIMPLE_ARRAY', 'smallint' => 'SMALLINT', 'string' => 'STRING', 'text' => 'TEXT', 'time' => 'TIME_MUTABLE', 'time_immutable' => 'TIME_IMMUTABLE'];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace string type in #[Column] attribute with Types class constant', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Column;

class SomeEntity
{
    #[Column(type: 'string')]
    public $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Column;

class SomeEntity
{
    #[Column(type: \Doctrine\DBAL\Types\Types::STRING)]
    public $name;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class, Param::class];
    }
    /**
     * @param Property|Param $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->isName($attribute->name, MappingClass::COLUMN)) {
                    continue;
                }
                foreach ($attribute->args as $arg) {
                    if (!$arg->name instanceof Identifier || $arg->name->toString() !== 'type') {
                        continue;
                    }
                    if (!$arg->value instanceof String_) {
                        continue;
                    }
                    $constantName = self::TYPE_TO_CONSTANT[$arg->value->value] ?? null;
                    if ($constantName === null) {
                        continue;
                    }
                    $arg->value = $this->nodeFactory->createClassConstFetch(self::TYPES_CLASS, $constantName);
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
