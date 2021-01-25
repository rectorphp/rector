<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Naming\ExpectedNameResolver\UnderscoreCamelCaseExpectedNameResolver;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\UnderscoreToCamelCaseVariableNameRectorTest
 */
final class UnderscoreToCamelCaseVariableNameRector extends AbstractRector
{
    /**
     * @var ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;

    /**
     * @var ParamRenameFactory
     */
    private $paramRenameFactory;

    /**
     * @var UnderscoreCamelCaseExpectedNameResolver
     */
    private $underscoreCamelCaseExpectedNameResolver;

    /**
     * @var ParamRenamer
     */
    private $paramRenamer;

    public function __construct(
        ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
        ParamRenameFactory $paramRenameFactory,
        ParamRenamer $underscoreCamelCaseParamRenamer,
        UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver
    ) {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->paramRenameFactory = $paramRenameFactory;
        $this->underscoreCamelCaseExpectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
        $this->paramRenamer = $underscoreCamelCaseParamRenamer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change under_score names to camelCase', [
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
        if ($camelCaseName === '') {
            return null;
        }
        if (is_numeric($camelCaseName[0])) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Param) {
            return $this->renameParam($parent);
        }

        $node->name = $camelCaseName;

        return $node;
    }

    private function renameParam(Param $param): ?Variable
    {
        $paramRename = $this->paramRenameFactory->create($param, $this->underscoreCamelCaseExpectedNameResolver);
        if (! $paramRename instanceof ParamRename) {
            return null;
        }

        $renamedParam = $this->paramRenamer->rename($paramRename);
        if (! $renamedParam instanceof Param) {
            return null;
        }

        /** @var Variable $variable */
        $variable = $renamedParam->var;

        return $variable;
    }
}
