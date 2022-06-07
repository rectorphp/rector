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
final class DowngradePhp81ResourceReturnToObjectRector extends AbstractRector
{
    /**
     * @var string[]|class-string<finfo>[]
     */
    private const COLLECTION_OBJECT_TO_RESOURCE = [
        // finfo
        'finfo',
        // ftp
        'RectorPrefix20220607\\FTP\\Connection',
        // imap_open
        'RectorPrefix20220607\\IMAP\\Connection',
        // pspell
        'RectorPrefix20220607\\PSpell\\Config',
        'RectorPrefix20220607\\PSpell\\Dictionary',
        // ldap
        'RectorPrefix20220607\\LDAP\\Connection',
        'RectorPrefix20220607\\LDAP\\Result',
        'RectorPrefix20220607\\LDAP\\ResultEntry',
        // psql
        'RectorPrefix20220607\\PgSql\\Connection',
        'RectorPrefix20220607\\PgSql\\Result',
        'RectorPrefix20220607\\PgSql\\Lob',
    ];
    /**
     * @readonly
     * @var \Rector\DowngradePhp81\NodeManipulator\ObjectToResourceReturn
     */
    private $objectToResourceReturn;
    public function __construct(ObjectToResourceReturn $objectToResourceReturn)
    {
        $this->objectToResourceReturn = $objectToResourceReturn;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('change instanceof Object to is_resource', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Instanceof_::class];
    }
    /**
     * @param Instanceof_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->objectToResourceReturn->refactor($node, self::COLLECTION_OBJECT_TO_RESOURCE);
    }
}
