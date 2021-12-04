<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\Instanceof_;

use finfo;
use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.resource2object
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\DowngradePhp81ResourceReturnToObjectRectorTest
 */
final class DowngradePhp81ResourceReturnToObjectRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]|class-string<finfo>[]
     */
    private const COLLECTION_OBJECT_TO_RESOURCE = [
        // finfo
        'finfo',
        // ftp
        'FTP\\Connection',
        // imap_open
        'IMAP\\Connection',
        // pspell
        'PSpell\\Config',
        'PSpell\\Dictionary',
        // ldap
        'LDAP\\Connection',
        'LDAP\\Result',
        'LDAP\\ResultEntry',
        // psql
        'PgSql\\Connection',
        'PgSql\\Result',
        'PgSql\\Lob',
    ];
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn
     */
    private $objectToResourceReturn;
    public function __construct(\Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn $objectToResourceReturn)
    {
        $this->objectToResourceReturn = $objectToResourceReturn;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('change instanceof Object to is_resource', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        $obj instanceof \finfo;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($obj)
    {
        is_resource($obj) || $obj instanceof \finfo;
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
        return [\PhpParser\Node\Expr\Instanceof_::class];
    }
    /**
     * @param Instanceof_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        return $this->objectToResourceReturn->refactor($node, self::COLLECTION_OBJECT_TO_RESOURCE);
    }
}
