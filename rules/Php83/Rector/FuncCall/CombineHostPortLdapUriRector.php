<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://www.php.net/manual/en/migration83.deprecated.php#migration83.deprecated.ldap
 * @see \Rector\Tests\Php83\Rector\FuncCall\CombineHostPortLdapUriRector\CombineHostPortLdapUriRectorTest
 */
final class CombineHostPortLdapUriRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Combine separated host and port on ldap_connect() args', [new CodeSample(<<<'CODE_SAMPLE'
ldap_connect('ldap://ldap.example.com', 389);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
ldap_connect('ldap://ldap.example.com:389');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'ldap_connect')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (\count($args) !== 2) {
            return null;
        }
        $firstArg = $args[0]->value;
        $secondArg = $args[1]->value;
        if ($firstArg instanceof String_ && $secondArg instanceof LNumber) {
            $args[0]->value = new String_($firstArg->value . ':' . $secondArg->value);
            unset($args[1]);
            $node->args = $args;
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_HOST_PORT_SEPARATE_ARGS;
    }
}
