<?php

declare (strict_types=1);
namespace Rector\Php53\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector\ReplaceHttpServerVarsByServerRectorTest
 */
final class ReplaceHttpServerVarsByServerRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var array<string, string>
     */
    private const VARIABLE_RENAME_MAP = ['HTTP_SERVER_VARS' => '_SERVER', 'HTTP_GET_VARS' => '_GET', 'HTTP_POST_VARS' => '_POST', 'HTTP_POST_FILES' => '_FILES', 'HTTP_SESSION_VARS' => '_SESSION', 'HTTP_ENV_VARS' => '_ENV', 'HTTP_COOKIE_VARS' => '_COOKIE'];
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::SERVER_VAR;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename old $HTTP_* variable names to new replacements', [new CodeSample('$serverVars = $HTTP_SERVER_VARS;', '$serverVars = $_SERVER;')]);
    }
    /**
     * @return array<class-string<Node>>
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
        $variableName = $this->getName($node);
        if ($variableName === null) {
            return null;
        }
        if (!isset(self::VARIABLE_RENAME_MAP[$variableName])) {
            return null;
        }
        $node->name = self::VARIABLE_RENAME_MAP[$variableName];
        return $node;
    }
}
