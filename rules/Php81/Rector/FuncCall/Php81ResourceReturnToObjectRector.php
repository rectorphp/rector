<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeManipulator\ResourceReturnToObject;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.resource2object
 *
 * @see \Rector\Tests\Php81\Rector\FuncCall\Php81ResourceReturnToObjectRector\Php81ResourceReturnToObjectRectorTest
 */
final class Php81ResourceReturnToObjectRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var array<string, string>
     */
    private const COLLECTION_FUNCTION_TO_RETURN_OBJECT = [
        // finfo
        'finfo_open' => 'finfo',
        // ftp
        'ftp_connect' => 'FTP\\Connection',
        // imap_open
        'imap_open' => 'IMAP\\Connection',
        // pspell
        'pspell_config_create' => 'PSpell\\Config',
        'pspell_new_config' => 'PSpell\\Dictionary',
        // ldap
        'ldap_connect' => 'LDAP\\Connection',
        'ldap_read' => 'LDAP\\Result',
        'ldap_first_entry' => 'LDAP\\ResultEntry',
        'ldap_first_reference' => 'LDAP\\ResultEntry',
        'ldap_next_entry' => 'LDAP\\ResultEntry',
        'ldap_next_reference' => 'LDAP\\ResultEntry',
        // psql
        'pg_pconnect' => 'PgSql\\Connection',
        'pg_connect' => 'PgSql\\Connection',
        'pg_query' => 'PgSql\\Result',
        'pg_prepare' => 'PgSql\\Result',
        'pg_execute' => 'PgSql\\Result',
        'pg_lo_open' => 'PgSql\\Lob',
    ];
    /**
     * @readonly
     * @var \Rector\Php80\NodeManipulator\ResourceReturnToObject
     */
    private $resourceReturnToObject;
    public function __construct(\Rector\Php80\NodeManipulator\ResourceReturnToObject $resourceReturnToObject)
    {
        $this->resourceReturnToObject = $resourceReturnToObject;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change is_resource() to instanceof Object', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $f = finfo_open();
        is_resource($f);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $f = finfo_open();
        $f instanceof \finfo;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\BinaryOp\BooleanOr::class];
    }
    /**
     * @param FuncCall|BooleanOr $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        return $this->resourceReturnToObject->refactor($node, self::COLLECTION_FUNCTION_TO_RETURN_OBJECT);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::PHP81_RESOURCE_TO_OBJECT;
    }
}
