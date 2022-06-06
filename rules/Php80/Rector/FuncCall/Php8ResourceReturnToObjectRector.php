<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\Php80\NodeManipulator\ResourceReturnToObject;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration80.incompatible.php#migration80.incompatible.resource2object
 *
 * @see \Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Php8ResourceReturnToObjectRectorTest
 */
final class Php8ResourceReturnToObjectRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(ResourceReturnToObject $resourceReturnToObject)
    {
        $this->resourceReturnToObject = $resourceReturnToObject;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change is_resource() to instanceof Object', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class, BooleanOr::class];
    }
    /**
     * @param FuncCall|BooleanOr $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->resourceReturnToObject->refactor($node, self::COLLECTION_FUNCTION_TO_RETURN_OBJECT);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PHP8_RESOURCE_TO_OBJECT;
    }
}
