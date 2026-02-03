<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\AuthorizationCheckerToAccessDecisionManagerInVoterRector\AuthorizationCheckerToAccessDecisionManagerInVoterRectorTest
 */
final class AuthorizationCheckerToAccessDecisionManagerInVoterRector extends AbstractRector
{
    /**
     * @var string
     */
    private const AUTHORIZATION_CHECKER_PROPERTY = 'authorizationChecker';
    /**
     * @var string
     */
    private const ACCESS_DECISION_MANAGER_PROPERTY = 'accessDecisionManager';
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces AuthorizationCheckerInterface with AccessDecisionManagerInterface inside Symfony Voters', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class AuthorizationCheckerVoter extends Voter
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class AuthorizationCheckerVoter extends Voter
{
    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager
    ) {}

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return $this->accessDecisionManager->decide($token, ['ROLE_ADMIN']);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null || !$this->isName($node->extends, SymfonyClass::VOTER_CLASS)) {
            return null;
        }
        $hasChanged = \false;
        $renamedProperties = [];
        $objectType = new ObjectType(SymfonyClass::AUTHORIZATION_CHECKER);
        // 1) Regular properties
        foreach ($node->getProperties() as $property) {
            if (!$this->isObjectType($property, $objectType)) {
                continue;
            }
            $property->type = new FullyQualified(SymfonyClass::ACCESS_DECISION_MANAGER_INTERFACE);
            foreach ($property->props as $prop) {
                if ($this->getName($prop) === self::AUTHORIZATION_CHECKER_PROPERTY) {
                    $prop->name = new Identifier(self::ACCESS_DECISION_MANAGER_PROPERTY);
                    $renamedProperties[self::AUTHORIZATION_CHECKER_PROPERTY] = self::ACCESS_DECISION_MANAGER_PROPERTY;
                }
            }
            $hasChanged = \true;
        }
        // 2) Promoted properties (constructor)
        $constructor = $node->getMethod('__construct');
        if ($constructor instanceof ClassMethod) {
            foreach ($constructor->params as $param) {
                if ($param->type === null || !$this->isName($param->type, SymfonyClass::AUTHORIZATION_CHECKER)) {
                    continue;
                }
                $param->type = new FullyQualified(SymfonyClass::ACCESS_DECISION_MANAGER_INTERFACE);
                if ($param->var instanceof Variable && $this->getName($param->var) === self::AUTHORIZATION_CHECKER_PROPERTY) {
                    $param->var->name = self::ACCESS_DECISION_MANAGER_PROPERTY;
                    $renamedProperties[self::AUTHORIZATION_CHECKER_PROPERTY] = self::ACCESS_DECISION_MANAGER_PROPERTY;
                }
                $hasChanged = \true;
            }
        }
        // 3) Replace isGranted() with decide()
        $voteMethod = $node->getMethod('voteOnAttribute');
        if ($voteMethod instanceof ClassMethod) {
            $this->traverseNodesWithCallable($voteMethod, function (Node $node) use (&$hasChanged, $voteMethod, $renamedProperties) {
                if ($node instanceof Class_ || $node instanceof Function_) {
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if (!$node instanceof MethodCall) {
                    return null;
                }
                if (!$this->isObjectType($node->var, new ObjectType(SymfonyClass::AUTHORIZATION_CHECKER))) {
                    return null;
                }
                if (!$node->var instanceof PropertyFetch) {
                    return null;
                }
                if (!$this->isName($node->name, 'isGranted')) {
                    return null;
                }
                $propertyName = $this->getName($node->var->name);
                if ($propertyName === null || !isset($renamedProperties[$propertyName])) {
                    return null;
                }
                $node->var->name = new Identifier($renamedProperties[$propertyName]);
                $node->name = new Identifier('decide');
                $tokenVariable = $voteMethod->params[2]->var ?? null;
                if (!$tokenVariable instanceof Variable) {
                    return null;
                }
                $attributeArg = $node->args[0] ?? null;
                if (!$attributeArg instanceof Arg) {
                    return null;
                }
                $attributeExpr = $attributeArg->value;
                $node->args = [new Arg($tokenVariable), new Arg(new Array_([new ArrayItem($attributeExpr)]))];
                $hasChanged = \true;
                return $node;
            });
        }
        return $hasChanged ? $node : null;
    }
}
