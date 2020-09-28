<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\UnderscoreToCamelCaseVariableNameRectorTest
 */
final class UnderscoreToCamelCaseVariableNameRector extends AbstractRector
{
    /**
     * @var ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;

    /**
     * @var string
     */
    private const PARAM_NAME_REGEX = '#(?<paramPrefix>@param\s.*\s+\$)(?<paramName>%s)#ms';

    public function __construct(ReservedKeywordAnalyzer $reservedKeywordAnalyzer)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change under_score names to camelCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a_b)
    {
        $some_value = $a_b;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($aB)
    {
        $someValue = $aB;
    }
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }

        if (! Strings::contains($nodeName, '_')) {
            return null;
        }

        if ($this->reservedKeywordAnalyzer->isNativeVariable($nodeName)) {
            return null;
        }

        $camelCaseName = StaticRectorStrings::underscoreToCamelCase($nodeName);
        if ($camelCaseName === 'this') {
            return null;
        }

        $node->name = $camelCaseName;
        $this->updateDocblock($node, $nodeName, $camelCaseName);

        return $node;
    }

    private function updateDocblock(Variable $variable, string $variableName, string $camelCaseName): void
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentNode) {
            /** @var ClassMethod|Function_ $parentNode */
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof ClassMethod || $parentNode instanceof Function_) {
                break;
            }
        }

        if ($parentNode === null) {
            return;
        }

        $docComment = $parentNode->getDocComment();
        if ($docComment === null) {
            return;
        }

        if ($docComment->getText() === null) {
            return;
        }

        if (! Strings::match($docComment->getText(), sprintf(self::PARAM_NAME_REGEX, $variableName))) {
            return;
        }

        $newdocComment = Strings::replace($docComment->getText(), sprintf(self::PARAM_NAME_REGEX, $variableName), function ($match) use ($camelCaseName): string {
            $match['paramName'] = $camelCaseName;
            return $match['paramPrefix'] . $match['paramName'];
        });

        $parentNode->setDocComment(new Doc($newdocComment));
    }
}
