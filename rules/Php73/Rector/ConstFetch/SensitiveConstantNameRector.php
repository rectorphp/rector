<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/case_insensitive_constant_deprecation
 *
 * @see \Rector\Tests\Php73\Rector\ConstFetch\SensitiveConstantNameRector\SensitiveConstantNameRectorTest
 */
final class SensitiveConstantNameRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @see http://php.net/manual/en/reserved.constants.php
     * @var string[]
     */
    private const PHP_RESERVED_CONSTANTS = ['PHP_VERSION', 'PHP_MAJOR_VERSION', 'PHP_MINOR_VERSION', 'PHP_RELEASE_VERSION', 'PHP_VERSION_ID', 'PHP_EXTRA_VERSION', 'PHP_ZTS', 'PHP_DEBUG', 'PHP_MAXPATHLEN', 'PHP_OS', 'PHP_OS_FAMILY', 'PHP_SAPI', 'PHP_EOL', 'PHP_INT_MAX', 'PHP_INT_MIN', 'PHP_INT_SIZE', 'PHP_FLOAT_DIG', 'PHP_FLOAT_EPSILON', 'PHP_FLOAT_MIN', 'PHP_FLOAT_MAX', 'DEFAULT_INCLUDE_PATH', 'PEAR_INSTALL_DIR', 'PEAR_EXTENSION_DIR', 'PHP_EXTENSION_DIR', 'PHP_PREFIX', 'PHP_BINDIR', 'PHP_BINARY', 'PHP_MANDIR', 'PHP_LIBDIR', 'PHP_DATADIR', 'PHP_SYSCONFDIR', 'PHP_LOCALSTATEDIR', 'PHP_CONFIG_FILE_PATH', 'PHP_CONFIG_FILE_SCAN_DIR', 'PHP_SHLIB_SUFFIX', 'PHP_FD_SETSIZE', 'E_ERROR', 'E_WARNING', 'E_PARSE', 'E_NOTICE', 'E_CORE_ERROR', 'E_CORE_WARNING', 'E_COMPILE_ERROR', 'E_COMPILE_WARNING', 'E_USER_ERROR', 'E_USER_WARNING', 'E_USER_NOTICE', 'E_RECOVERABLE_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED', 'E_ALL', 'E_STRICT', '__COMPILER_HALT_OFFSET__', 'TRUE', 'FALSE', 'NULL'];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_INSENSITIVE_CONSTANT_NAME;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes case insensitive constants to sensitive ones.', [new CodeSample(<<<'CODE_SAMPLE'
define('FOO', 42, true);
var_dump(FOO);
var_dump(foo);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
define('FOO', 42, true);
var_dump(FOO);
var_dump(FOO);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $constantName = $this->getName($node);
        if ($constantName === null) {
            return null;
        }
        $uppercasedConstantName = \strtoupper($constantName);
        // is system constant?
        if (\in_array($uppercasedConstantName, self::PHP_RESERVED_CONSTANTS, \true)) {
            return null;
        }
        // constant is defined in current lower/upper case
        if ($this->reflectionProvider->hasConstant(new Name($constantName), $scope)) {
            return null;
        }
        // is uppercase, all good
        if ($constantName === $uppercasedConstantName) {
            return null;
        }
        if (\strpos($uppercasedConstantName, '\\') !== \false) {
            return null;
        }
        $node->name = new FullyQualified($uppercasedConstantName);
        return $node;
    }
}
