<?php

declare (strict_types=1);
namespace Rector\Symfony\SwiftMailer\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\SwiftMailer\Rector\ClassMethod\SwiftMessageToEmailRector\SwiftMessageToEmailRectorTest
 */
class SwiftMessageToEmailRector extends AbstractRector
{
    public const EMAIL_FQN = 'Symfony\\Component\\Mime\\Email';
    public const SWIFT_MESSAGE_FQN = 'Swift_Message';
    /**
     * @var array<string, string>
     */
    private $basicMapping = ['setSubject' => 'subject', 'setPriority' => 'priority'];
    /**
     * @var array<string, ?string>
     */
    private $addressesMapping = ['addBcc' => null, 'addCc' => null, 'addFrom' => null, 'addReplyTo' => null, 'addTo' => null, 'setBcc' => 'bcc', 'setCc' => 'cc', 'setFrom' => 'from', 'setReplyTo' => 'replyTo', 'setTo' => 'to'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert \\Swift_Message into an \\Symfony\\Component\\Mime\\Email', [new CodeSample(<<<'CODE_SAMPLE'
$message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo(['recipient@example.com' => 'Recipient'])
        ->setBody(
            $this->renderView(
                'emails/registration.html.twig',
                ['name' => $name]
            ),
            'text/html'
        )
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$message = (new Email())
    ->from(new Address('send@example.com'))
    ->to(new Address('recipient@example.com', 'Recipient'))
    ->subject('Hello Email')
    ->html($this->renderView(
        'emails/registration.html.twig',
        ['name' => $name]
    ))
;
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->traverseNodesWithCallable($node, function (Node $node) : ?Node {
            if ($node instanceof ClassMethod && $node->returnType instanceof FullyQualified && $this->isName($node->returnType, self::SWIFT_MESSAGE_FQN)) {
                $node->returnType = new FullyQualified(self::EMAIL_FQN);
            }
            if ($node instanceof Param && $node->type instanceof FullyQualified && $this->isName($node->type, self::SWIFT_MESSAGE_FQN)) {
                $node->type = new FullyQualified(self::EMAIL_FQN);
            }
            if ($node instanceof New_) {
                if (!$this->isName($node->class, self::SWIFT_MESSAGE_FQN)) {
                    return null;
                }
                $args = $node->getArgs();
                if ($args !== []) {
                    $node = new MethodCall(new New_(new FullyQualified(self::EMAIL_FQN)), 'subject', [$args[0]]);
                } else {
                    $node->class = new FullyQualified(self::EMAIL_FQN);
                }
            }
            if ($node instanceof MethodCall) {
                $name = $this->getName($node->name);
                if ($name) {
                    $objectType = $this->nodeTypeResolver->getType($node->var);
                    if (!$objectType instanceof ObjectType) {
                        return null;
                    }
                    if (!$objectType->isInstanceOf(self::SWIFT_MESSAGE_FQN)->yes() && !$objectType->isInstanceOf(self::EMAIL_FQN)->yes()) {
                        return null;
                    }
                    $this->handleBasicMapping($node, $name);
                    $this->handleAddressMapping($node, $name);
                    $this->handleBody($node, $name);
                    if ($name === 'attach') {
                        $this->handleAttach($node);
                    }
                    if ($name === 'getId') {
                        $node = $this->handleId($node);
                    }
                }
            }
            return $node;
        });
        return $node;
    }
    private function handleBasicMapping(MethodCall $methodCall, string $name) : void
    {
        if (\array_key_exists($name, $this->basicMapping)) {
            $methodCall->name = new Identifier($this->basicMapping[$name]);
        }
    }
    private function handleAddressMapping(MethodCall $methodCall, string $name) : void
    {
        if (\array_key_exists($name, $this->addressesMapping)) {
            if ($this->addressesMapping[$name] !== null) {
                $methodCall->name = new Identifier($this->addressesMapping[$name]);
            }
            if ($methodCall->getArgs() === []) {
                return;
            }
            if (!($firstArg = $methodCall->args[0]) instanceof Arg) {
                return;
            }
            if ($firstArg->value instanceof Array_ && $firstArg->value->items !== []) {
                $newArgs = [];
                foreach ($firstArg->value->items as $item) {
                    if ($item instanceof ArrayItem) {
                        $newArgs[] = $this->nodeFactory->createArg($this->createAddress($item->key === null ? [new Arg($item->value)] : [new Arg($item->key), new Arg($item->value)]));
                    }
                }
                $methodCall->args = $newArgs;
            } else {
                $addressArguments = [new Arg($firstArg->value)];
                if (isset($methodCall->args[1]) && ($secondArg = $methodCall->args[1]) instanceof Arg) {
                    $addressArguments[] = new Arg($secondArg->value);
                }
                $methodCall->args = [new Arg($this->createAddress($addressArguments))];
            }
        }
    }
    private function handleBody(MethodCall $methodCall, string $name) : void
    {
        if (!\in_array($name, ['setBody', 'addPart'], \true)) {
            return;
        }
        if ($methodCall->args[1] instanceof Arg && $methodCall->args[1]->value instanceof String_ && $methodCall->args[1]->value->value === 'text/html') {
            $methodCall->name = new Identifier('html');
        } else {
            $methodCall->name = new Identifier('text');
        }
        $methodCall->args = [$methodCall->args[0]];
    }
    private function handleAttach(MethodCall $methodCall) : void
    {
        $this->traverseNodesWithCallable($methodCall->args[0], function (Node $node) use($methodCall) : Node {
            if ($node instanceof StaticCall && $this->isName($node->name, 'fromPath')) {
                $methodCall->args[0] = $node->args[0];
            }
            if ($node instanceof MethodCall) {
                if ($this->isName($node->name, 'setFilename')) {
                    $methodCall->args[1] = $node->args[0];
                }
                if ($this->isName($node->name, 'setContentType')) {
                    $methodCall->args[2] = $node->args[0];
                }
            }
            return $node;
        });
        $methodCall->name = new Identifier('attachFromPath');
    }
    private function handleId(MethodCall $methodCall) : MethodCall
    {
        $methodCall->name = new Identifier('getHeaders');
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($methodCall, 'get', [$this->nodeFactory->createArg(new String_('Content-ID'))]), 'toString');
    }
    /**
     * @param Arg[] $addressArguments
     */
    private function createAddress(array $addressArguments) : New_
    {
        return new New_(new FullyQualified('Symfony\\Component\\Mime\\Address'), $addressArguments);
    }
}
