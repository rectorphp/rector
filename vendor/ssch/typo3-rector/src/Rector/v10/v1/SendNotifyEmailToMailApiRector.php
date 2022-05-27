<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v1;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Deprecation-88850-ContentObjectRendererSendNotifyEmail.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v1\SendNotifyEmailToMailApiRector\SendNotifyEmailToMailApiRectorTest
 */
final class SendNotifyEmailToMailApiRector extends AbstractRector
{
    /**
     * @var string
     */
    private const MAIL = 'mail';
    /**
     * @var string
     */
    private const MESSAGE = 'message';
    /**
     * @var string
     */
    private const TRIM = 'trim';
    /**
     * @var string
     */
    private const SENDER_ADDRESS = 'senderAddress';
    /**
     * @var string
     */
    private const MESSAGE_PARTS = 'messageParts';
    /**
     * @var string
     */
    private const SUBJECT = 'subject';
    /**
     * @var string
     */
    private const PARSED_RECIPIENTS = 'parsedRecipients';
    /**
     * @var string
     */
    private const SUCCESS = 'success';
    /**
     * @var string
     */
    private const PARSED_REPLY_TO = 'parsedReplyTo';
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return null;
        }
        if (!$this->isName($node->name, 'sendNotifyEmail')) {
            return null;
        }
        $this->nodesToAddCollector->addNodesBeforeNode([$this->initializeSuccessVariable(), $this->initializeMailClass(), $this->trimMessage($node), $this->trimSenderName($node), $this->trimSenderAddress($node), $this->ifSenderAddress()], $node);
        $replyTo = isset($node->args[5]) ? $node->args[5]->value : null;
        if (null !== $replyTo) {
            $this->nodesToAddCollector->addNodeBeforeNode($this->parsedReplyTo($replyTo), $node);
            $this->nodesToAddCollector->addNodeBeforeNode($this->methodReplyTo(), $node);
        }
        $ifMessageNotEmpty = $this->messageNotEmpty();
        $ifMessageNotEmpty->stmts[] = $this->messageParts();
        $ifMessageNotEmpty->stmts[] = $this->subjectFromMessageParts();
        $ifMessageNotEmpty->stmts[] = $this->bodyFromMessageParts();
        $ifMessageNotEmpty->stmts[] = $this->parsedRecipients($node);
        $ifMessageNotEmpty->stmts[] = $this->ifParsedRecipients();
        $ifMessageNotEmpty->stmts[] = $this->createSuccessTrue();
        $this->nodesToAddCollector->addNodeBeforeNode($ifMessageNotEmpty, $node);
        return new Variable(self::SUCCESS);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor ContentObjectRenderer::sendNotifyEmail to MailMessage-API', [new CodeSample(<<<'CODE_SAMPLE'
$GLOBALS['TSFE']->cObj->sendNotifyEmail("Subject\nMessage", 'max.mustermann@domain.com', 'max.mustermann@domain.com', 'max.mustermann@domain.com');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;$success = false;

$mail = GeneralUtility::makeInstance(MailMessage::class);
$message = trim("Subject\nMessage");
$senderName = trim(null);
$senderAddress = trim('max.mustermann@domain.com');

if ($senderAddress !== '') {
    $mail->from(new Address($senderAddress, $senderName));
}

if ($message !== '') {
    $messageParts = explode(LF, $message, 2);
    $subject = trim($messageParts[0]);
    $plainMessage = trim($messageParts[1]);
    $parsedRecipients = MailUtility::parseAddresses('max.mustermann@domain.com');
    if (!empty($parsedRecipients)) {
        $mail->to(...$parsedRecipients)->subject($subject)->text($plainMessage);
        $mail->send();
    }
    $success = true;
}
CODE_SAMPLE
)]);
    }
    private function initializeSuccessVariable() : Node
    {
        return new Expression(new Assign(new Variable(self::SUCCESS), $this->nodeFactory->createFalse()));
    }
    private function initializeMailClass() : Node
    {
        return new Expression(new Assign(new Variable(self::MAIL), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Mail\\MailMessage')])));
    }
    private function trimMessage(MethodCall $methodCall) : Node
    {
        return new Assign(new Variable(self::MESSAGE), $this->nodeFactory->createFuncCall(self::TRIM, [$methodCall->args[0]]));
    }
    private function trimSenderName(MethodCall $methodCall) : Node
    {
        return new Expression(new Assign(new Variable('senderName'), $this->nodeFactory->createFuncCall(self::TRIM, [$methodCall->args[4] ?? new ConstFetch(new Name('null'))])));
    }
    private function trimSenderAddress(MethodCall $methodCall) : Node
    {
        return new Expression(new Assign(new Variable(self::SENDER_ADDRESS), $this->nodeFactory->createFuncCall(self::TRIM, [$methodCall->args[3]])));
    }
    private function mailFromMethodCall() : MethodCall
    {
        return $this->nodeFactory->createMethodCall(self::MAIL, 'from', [new New_(new FullyQualified('Symfony\\Component\\Mime\\Address'), [$this->nodeFactory->createArg(new Variable(self::SENDER_ADDRESS)), $this->nodeFactory->createArg(new Variable('senderName'))])]);
    }
    private function ifSenderAddress() : Node
    {
        $mailFromMethodCall = $this->mailFromMethodCall();
        $ifSenderName = new If_(new NotIdentical(new Variable(self::SENDER_ADDRESS), new String_('')));
        $ifSenderName->stmts[0] = new Expression($mailFromMethodCall);
        return $ifSenderName;
    }
    private function messageNotEmpty() : If_
    {
        return new If_(new NotIdentical(new Variable(self::MESSAGE), new String_('')));
    }
    private function messageParts() : Expression
    {
        return new Expression(new Assign(new Variable(self::MESSAGE_PARTS), $this->nodeFactory->createFuncCall('explode', [new ConstFetch(new Name('LF')), new Variable(self::MESSAGE), new LNumber(2)])));
    }
    private function subjectFromMessageParts() : Expression
    {
        return new Expression(new Assign(new Variable(self::SUBJECT), $this->nodeFactory->createFuncCall(self::TRIM, [new ArrayDimFetch(new Variable(self::MESSAGE_PARTS), new LNumber(0))])));
    }
    private function bodyFromMessageParts() : Expression
    {
        return new Expression(new Assign(new Variable('plainMessage'), $this->nodeFactory->createFuncCall(self::TRIM, [new ArrayDimFetch(new Variable(self::MESSAGE_PARTS), new LNumber(1))])));
    }
    private function parsedRecipients(MethodCall $methodCall) : Expression
    {
        return new Expression(new Assign(new Variable(self::PARSED_RECIPIENTS), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\MailUtility', 'parseAddresses', [$methodCall->args[1]])));
    }
    private function ifParsedRecipients() : If_
    {
        $ifParsedRecipients = new If_(new BooleanNot(new Empty_(new Variable(self::PARSED_RECIPIENTS))));
        $ifParsedRecipients->stmts[] = new Expression($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(self::MAIL, 'to', [new Arg(new Variable(self::PARSED_RECIPIENTS), \false, \true)]), self::SUBJECT, [new Variable(self::SUBJECT)]), 'text', [new Variable('plainMessage')]));
        $ifParsedRecipients->stmts[] = new Expression($this->nodeFactory->createMethodCall(self::MAIL, 'send'));
        return $ifParsedRecipients;
    }
    private function createSuccessTrue() : Expression
    {
        return new Expression(new Assign(new Variable(self::SUCCESS), $this->nodeFactory->createTrue()));
    }
    private function parsedReplyTo(Expr $replyTo) : Node
    {
        return new Expression(new Assign(new Variable(self::PARSED_REPLY_TO), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\MailUtility', 'parseAddresses', [$replyTo])));
    }
    private function methodReplyTo() : Node
    {
        $if = new If_(new BooleanNot(new Empty_(new Variable(self::PARSED_REPLY_TO))));
        $if->stmts[] = new Expression($this->nodeFactory->createMethodCall(self::MAIL, 'setReplyTo', [new Variable(self::PARSED_REPLY_TO)]));
        return $if;
    }
}
