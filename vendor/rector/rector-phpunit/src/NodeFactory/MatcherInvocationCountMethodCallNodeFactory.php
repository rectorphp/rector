<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\PHPUnit\Composer\ProjectPackageVersionResolver;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
final class MatcherInvocationCountMethodCallNodeFactory
{
    /**
     * @readonly
     */
    private ProjectPackageVersionResolver $projectPackageVersionResolver;
    public function __construct(ProjectPackageVersionResolver $projectPackageVersionResolver)
    {
        $this->projectPackageVersionResolver = $projectPackageVersionResolver;
    }
    public function create() : MethodCall
    {
        $invocationMethodName = $this->getInvocationMethodName();
        $matcherVariable = new Variable(ConsecutiveVariable::MATCHER);
        return new MethodCall($matcherVariable, new Identifier($invocationMethodName));
    }
    private function getInvocationMethodName() : string
    {
        $projectPHPUnitVersion = $this->projectPackageVersionResolver->findPackageVersion('phpunit/phpunit');
        if ($projectPHPUnitVersion === null || \version_compare($projectPHPUnitVersion, '10.0', '>=')) {
            // phpunit 10 naming
            return 'numberOfInvocations';
        }
        // phpunit 9 naming
        return 'getInvocationCount';
    }
}
