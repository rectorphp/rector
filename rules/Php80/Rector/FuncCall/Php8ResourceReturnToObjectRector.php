<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change is_resource() to instanceof Object',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $ch = curl_init();
        is_resource($ch);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $ch = curl_init();
        $ch instanceof \CurlHandle;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, BooleanOr::class];
    }

    /**
     * @param FuncCall|BooleanOr $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FuncCall) {
            return $this->processFuncCall($node);
        }

        return $this->processBooleanOr($node);
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::PHP8_RESOURCE_TO_OBJECT;
    }

    private function processFuncCall(FuncCall $funcCall): ?Instanceof_
    {
        $parent = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof BinaryOp && ! $parent instanceof BooleanOr) {
            return null;
        }

        if ($this->shouldSkip($funcCall)) {
            return null;
        }

        $objectInstanceCheck = $this->resolveObjectInstanceCheck($funcCall);
        if ($objectInstanceCheck === null) {
            return null;
        }

        /** @var Expr $argResourceValue */
        $argResourceValue = $funcCall->args[0]->value;
        return new Instanceof_($argResourceValue, new FullyQualified($objectInstanceCheck));
    }

    private function resolveArgValueType(FuncCall $funcCall): ?Type
    {
        /** @var Expr $argResourceValue */
        $argResourceValue = $funcCall->args[0]->value;
        $argValueType = $this->nodeTypeResolver->getType($argResourceValue);

        // if detected type is not FullyQualifiedObjectType, it still can be a resource to object, when:
        //      - in the right position of BooleanOr, it be NeverType
        //      - the object changed after init
        if (! $argValueType instanceof FullyQualifiedObjectType) {
            $argValueType = $this->resolveArgValueTypeFromPreviousAssign($funcCall, $argResourceValue);
        }

        return $argValueType;
    }

    private function resolveObjectInstanceCheck(FuncCall $funcCall): ?string
    {
        $argValueType = $this->resolveArgValueType($funcCall);
        if (! $argValueType instanceof FullyQualifiedObjectType) {
            return null;
        }

        $className = $argValueType->getClassName();
        foreach (self::COLLECTION_FUNCTION_TO_RETURN_OBJECT as $value) {
            if ($className === $value) {
                return $value;
            }
        }

        return null;
    }

    private function resolveArgValueTypeFromPreviousAssign(FuncCall $funcCall, Expr $expr): ?FullyQualifiedObjectType
    {
        $objectInstanceCheck = null;
        $assign = $this->betterNodeFinder->findFirstPreviousOfNode($funcCall, function (Node $subNode) use (
            &$objectInstanceCheck,
            $expr
        ): bool {
            if (! $this->isAssignWithFuncCallExpr($subNode)) {
                return false;
            }

            /** @var Assign $subNode */
            if (! $this->nodeComparator->areNodesEqual($subNode->var, $expr)) {
                return false;
            }

            foreach (self::COLLECTION_FUNCTION_TO_RETURN_OBJECT as $key => $value) {
                if ($this->nodeNameResolver->isName($subNode->expr, $key)) {
                    $objectInstanceCheck = $value;
                    return true;
                }
            }

            return false;
        });

        if (! $assign instanceof Assign) {
            return null;
        }

        /** @var string $objectInstanceCheck */
        return new FullyQualifiedObjectType($objectInstanceCheck);
    }

    private function isAssignWithFuncCallExpr(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        return $node->expr instanceof FuncCall;
    }

    private function processBooleanOr(BooleanOr $booleanOr): ?Instanceof_
    {
        $left = $booleanOr->left;
        $right = $booleanOr->right;

        $funCall = null;
        $instanceof = null;

        if ($left instanceof FuncCall && $right instanceof Instanceof_) {
            $funCall = $left;
            $instanceof = $right;
        } elseif ($left instanceof Instanceof_ && $right instanceof FuncCall) {
            $funCall = $right;
            $instanceof = $left;
        } else {
            return null;
        }

        /** @var FuncCall $funCall */
        if ($this->shouldSkip($funCall)) {
            return null;
        }

        $objectInstanceCheck = $this->resolveObjectInstanceCheck($funCall);
        if ($objectInstanceCheck === null) {
            return null;
        }

        /** @var Expr $argResourceValue */
        $argResourceValue = $funCall->args[0]->value;
        /** @var Instanceof_ $instanceof */
        if (! $this->isInstanceOfObjectCheck($instanceof, $argResourceValue, $objectInstanceCheck)) {
            return null;
        }

        return $instanceof;
    }

    private function isInstanceOfObjectCheck(Instanceof_ $instanceof, Expr $expr, string $objectInstanceCheck): bool
    {
        if (! $instanceof->class instanceof FullyQualified) {
            return false;
        }

        if (! $this->nodeComparator->areNodesEqual($expr, $instanceof->expr)) {
            return false;
        }

        return $this->nodeNameResolver->isName($instanceof->class, $objectInstanceCheck);
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->nodeNameResolver->isName($funcCall, 'is_resource')) {
            return true;
        }

        if (! isset($funcCall->args[0])) {
            return true;
        }

        $argResource = $funcCall->args[0];
        return ! $argResource instanceof Arg;
    }
}
