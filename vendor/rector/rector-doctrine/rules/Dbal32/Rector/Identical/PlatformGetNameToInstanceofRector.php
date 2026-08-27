<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal32\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/dbal/pull/4755
 * @see Rector\Doctrine\Tests\Dbal32\Rector\Identical\PlatformGetNameToInstanceofRector\PlatformGetNameToInstanceofRectorTest
 */
final class PlatformGetNameToInstanceofRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * Maps the string returned by AbstractPlatform::getName() to its platform class.
     *
     * @var array<string, string>
     */
    private const PLATFORM_MAP = ['postgresql' => 'Doctrine\DBAL\Platforms\PostgreSQLPlatform', 'mysql' => 'Doctrine\DBAL\Platforms\MySQLPlatform', 'sqlite' => 'Doctrine\DBAL\Platforms\SqlitePlatform', 'oracle' => 'Doctrine\DBAL\Platforms\OraclePlatform', 'mssql' => 'Doctrine\DBAL\Platforms\SQLServerPlatform', 'db2' => 'Doctrine\DBAL\Platforms\DB2Platform'];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $platform->getName() === "postgresql" to $platform instanceof PostgreSQLPlatform, following the DBAL 3.2 deprecation of AbstractPlatform::getName(), see https://github.com/doctrine/dbal/pull/4755', [new CodeSample("if ('postgresql' === \$this->platform->getName()) {}", "if (\$this->platform instanceof \\Doctrine\\DBAL\\Platforms\\PostgreSQLPlatform) {}")]);
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('doctrine/dbal', '>= 3.2');
    }
    public function getNodeTypes(): array
    {
        return [Identical::class, Equal::class];
    }
    /**
     * @param Identical|Equal $node
     */
    public function refactor(Node $node): ?Node
    {
        // Handle both: 'postgresql' === $platform->getName() AND $platform->getName() === 'postgresql'
        if ($node->left instanceof String_ && $node->right instanceof MethodCall) {
            $stringNode = $node->left;
            $methodCallNode = $node->right;
        } elseif ($node->right instanceof String_ && $node->left instanceof MethodCall) {
            $stringNode = $node->right;
            $methodCallNode = $node->left;
        } else {
            return null;
        }
        if (!$this->isName($methodCallNode->name, 'getName')) {
            return null;
        }
        // ONLY apply this if the variable calling getName() is a Doctrine DBAL Platform
        if (!$this->isObjectType($methodCallNode->var, new ObjectType('Doctrine\DBAL\Platforms\AbstractPlatform'))) {
            return null;
        }
        $platformName = $stringNode->value;
        if (!isset(self::PLATFORM_MAP[$platformName])) {
            return null;
        }
        return new Instanceof_($methodCallNode->var, new FullyQualified(self::PLATFORM_MAP[$platformName]));
    }
}
