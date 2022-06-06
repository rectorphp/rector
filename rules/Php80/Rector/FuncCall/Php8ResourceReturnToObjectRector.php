<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FuncCall;

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
 * @changelog https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.resource2object
 *
 * @see \Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Php8ResourceReturnToObjectRectorTest
 */
final class Php8ResourceReturnToObjectRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var array<string, string>
     */
    private const COLLECTION_FUNCTION_TO_RETURN_OBJECT = [
        // curl
        'curl_init' => 'CurlHandle',
        'curl_multi_init' => 'CurlMultiHandle',
        'curl_share_init' => 'CurlShareHandle',
        // socket
        'socket_create' => 'Socket',
        'socket_accept' => 'Socket',
        'socket_addrinfo_bind' => 'Socket',
        'socket_addrinfo_connect' => 'Socket',
        'socket_create_listen' => 'Socket',
        'socket_import_stream' => 'Socket',
        'socket_wsaprotocol_info_import' => 'Socket',
        // GD
        'imagecreate' => 'GdImage',
        'imagecreatefromavif' => 'GdImage',
        'imagecreatefrombmp' => 'GdImage',
        'imagecreatefromgd2' => 'GdImage',
        'imagecreatefromgd2part' => 'GdImage',
        'imagecreatefromgd' => 'GdImage',
        'imagecreatefromgif' => 'GdImage',
        'imagecreatefromjpeg' => 'GdImage',
        'imagecreatefrompng' => 'GdImage',
        'imagecreatefromstring' => 'GdImage',
        'imagecreatefromtga' => 'GdImage',
        'imagecreatefromwbmp' => 'GdImage',
        'imagecreatefromwebp' => 'GdImage',
        'imagecreatefromxbm' => 'GdImage',
        'imagecreatefromxpm' => 'GdImage',
        'imagecreatetruecolor' => 'GdImage',
        'imagecrop' => 'GdImage',
        'imagecropauto' => 'GdImage',
        // XMLWriter
        'xmlwriter_open_memory' => 'XMLWriter',
        // XMLParser
        'xml_parser_create' => 'XMLParser',
        'xml_parser_create_ns' => 'XMLParser',
        // Broker
        'enchant_broker_init' => 'EnchantBroker',
        'enchant_broker_request_dict' => 'EnchantDictionary',
        'enchant_broker_request_pwl_dict' => 'EnchantDictionary',
        // OpenSSL
        'openssl_x509_read' => 'OpenSSLCertificate',
        'openssl_csr_sign' => 'OpenSSLCertificate',
        'openssl_csr_new' => 'OpenSSLCertificateSigningRequest',
        'openssl_pkey_new' => 'OpenSSLAsymmetricKey',
        // Shmop
        'shmop_open' => 'Shmop',
        // MessageQueue
        'msg_get_queue' => 'SysvMessageQueue',
        'sem_get' => 'SysvSemaphore',
        'shm_attach' => 'SysvSharedMemory',
        // Inflate Deflate
        'inflate_init' => 'InflateContext',
        'deflate_init' => 'DeflateContext',
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
        $ch = curl_init();
        is_resource($ch);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle;
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
        return \Rector\Core\ValueObject\PhpVersionFeature::PHP8_RESOURCE_TO_OBJECT;
    }
}
