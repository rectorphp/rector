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
final class SendNotifyEmailToMailApiRector extends \Rector\Core\Rector\AbstractRector
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
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
        return new \PhpParser\Node\Expr\Variable(self::SUCCESS);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor ContentObjectRenderer::sendNotifyEmail to MailMessage-API', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function initializeSuccessVariable() : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::SUCCESS), $this->nodeFactory->createFalse()));
    }
    private function initializeMailClass() : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::MAIL), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Mail\\MailMessage')])));
    }
    private function trimMessage(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node
    {
        return new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::MESSAGE), $this->nodeFactory->createFuncCall(self::TRIM, [$methodCall->args[0]]));
    }
    private function trimSenderName(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('senderName'), $this->nodeFactory->createFuncCall(self::TRIM, [$methodCall->args[4] ?? new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('null'))])));
    }
    private function trimSenderAddress(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::SENDER_ADDRESS), $this->nodeFactory->createFuncCall(self::TRIM, [$methodCall->args[3]])));
    }
    private function mailFromMethodCall() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createMethodCall(self::MAIL, 'from', [new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Mime\\Address'), [$this->nodeFactory->createArg(new \PhpParser\Node\Expr\Variable(self::SENDER_ADDRESS)), $this->nodeFactory->createArg(new \PhpParser\Node\Expr\Variable('senderName'))])]);
    }
    private function ifSenderAddress() : \PhpParser\Node
    {
        $mailFromMethodCall = $this->mailFromMethodCall();
        $ifSenderName = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BinaryOp\NotIdentical(new \PhpParser\Node\Expr\Variable(self::SENDER_ADDRESS), new \PhpParser\Node\Scalar\String_('')));
        $ifSenderName->stmts[0] = new \PhpParser\Node\Stmt\Expression($mailFromMethodCall);
        return $ifSenderName;
    }
    private function messageNotEmpty() : \PhpParser\Node\Stmt\If_
    {
        return new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BinaryOp\NotIdentical(new \PhpParser\Node\Expr\Variable(self::MESSAGE), new \PhpParser\Node\Scalar\String_('')));
    }
    private function messageParts() : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::MESSAGE_PARTS), $this->nodeFactory->createFuncCall('explode', [new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('LF')), new \PhpParser\Node\Expr\Variable(self::MESSAGE), new \PhpParser\Node\Scalar\LNumber(2)])));
    }
    private function subjectFromMessageParts() : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::SUBJECT), $this->nodeFactory->createFuncCall(self::TRIM, [new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(self::MESSAGE_PARTS), new \PhpParser\Node\Scalar\LNumber(0))])));
    }
    private function bodyFromMessageParts() : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('plainMessage'), $this->nodeFactory->createFuncCall(self::TRIM, [new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(self::MESSAGE_PARTS), new \PhpParser\Node\Scalar\LNumber(1))])));
    }
    private function parsedRecipients(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::PARSED_RECIPIENTS), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\MailUtility', 'parseAddresses', [$methodCall->args[1]])));
    }
    private function ifParsedRecipients() : \PhpParser\Node\Stmt\If_
    {
        $ifParsedRecipients = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Empty_(new \PhpParser\Node\Expr\Variable(self::PARSED_RECIPIENTS))));
        $ifParsedRecipients->stmts[] = new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall($this->nodeFactory->createMethodCall(self::MAIL, 'to', [new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Variable(self::PARSED_RECIPIENTS), \false, \true)]), self::SUBJECT, [new \PhpParser\Node\Expr\Variable(self::SUBJECT)]), 'text', [new \PhpParser\Node\Expr\Variable('plainMessage')]));
        $ifParsedRecipients->stmts[] = new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createMethodCall(self::MAIL, 'send'));
        return $ifParsedRecipients;
    }
    private function createSuccessTrue() : \PhpParser\Node\Stmt\Expression
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::SUCCESS), $this->nodeFactory->createTrue()));
    }
    private function parsedReplyTo(\PhpParser\Node\Expr $replyTo) : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::PARSED_REPLY_TO), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\MailUtility', 'parseAddresses', [$replyTo])));
    }
    private function methodReplyTo() : \PhpParser\Node
    {
        $if = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Empty_(new \PhpParser\Node\Expr\Variable(self::PARSED_REPLY_TO))));
        $if->stmts[] = new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createMethodCall(self::MAIL, 'setReplyTo', [new \PhpParser\Node\Expr\Variable(self::PARSED_REPLY_TO)]));
        return $if;
    }
}
