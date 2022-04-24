<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/10.2/en-us/Changelog/10.0/Feature-88643-NewMailAPIBasedOnSymfonymailerAndSymfonymime.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\SwiftMailerBasedMailMessageToMailerBasedMessageRector\SwiftMailerBasedMailMessageToMailerBasedMessageRectorTest
 */
final class SwiftMailerBasedMailMessageToMailerBasedMessageRector extends \Rector\Core\Rector\AbstractRector
{
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Mail\\MailMessage'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['setBody', 'addPart', 'attach', 'embed'])) {
            return null;
        }
        if ($this->isName($node->name, 'setBody')) {
            return $this->refactorMethodSetBody($node);
        }
        if ($this->isName($node->name, 'addPart')) {
            return $this->refactorMethodAddPart($node);
        }
        if ($this->isName($node->name, 'attach')) {
            return $this->refactorAttachMethod($node);
        }
        return $this->refactorEmbedMethod($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('New Mail API based on symfony/mailer and symfony/mime', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Swift_Attachment;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$mail = GeneralUtility::makeInstance(MailMessage::class);

$mail
    ->setSubject('Your subject')
    ->setFrom(['john@doe.com' => 'John Doe'])
    ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
    ->setBody('Here is the message itself')
    ->addPart('<p>Here is the message itself</p>', 'text/html')
    ->attach(Swift_Attachment::fromPath('my-document.pdf'))
    ->send();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$mail = GeneralUtility::makeInstance(MailMessage::class);

$mail
    ->setSubject('Your subject')
    ->setFrom(['john@doe.com' => 'John Doe'])
    ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
    ->text('Here is the message itself')
    ->html('<p>Here is the message itself</p>')
    ->attachFromPath('my-document.pdf')
    ->send();
CODE_SAMPLE
)]);
    }
    private function refactorMethodSetBody(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!isset($methodCall->args[0])) {
            return null;
        }
        if (!$methodCall->args[0]->value instanceof \PhpParser\Node) {
            return null;
        }
        $bodyType = $this->nodeTypeResolver->getType($methodCall->args[0]->value);
        $contentType = isset($methodCall->args[1]) ? $this->valueResolver->getValue($methodCall->args[1]->value) : null;
        if (!$bodyType instanceof \PHPStan\Type\StringType) {
            return null;
        }
        $methodIdentifier = 'text';
        if ('text/html' === $contentType) {
            $methodIdentifier = 'html';
        }
        if (null !== $contentType) {
            unset($methodCall->args[1]);
        }
        $methodCall->name = new \PhpParser\Node\Identifier($methodIdentifier);
        return $methodCall;
    }
    private function refactorMethodAddPart(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $contentType = isset($methodCall->args[1]) ? $this->valueResolver->getValue($methodCall->args[1]->value) : null;
        $methodCall->name = new \PhpParser\Node\Identifier('text');
        if (!\is_string($contentType)) {
            return null;
        }
        unset($methodCall->args[1]);
        if ('text/html' === $contentType) {
            $methodCall->name = new \PhpParser\Node\Identifier('html');
            return $methodCall;
        }
        return $methodCall;
    }
    private function refactorAttachMethod(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $firstArgument = $methodCall->args[0]->value;
        if (!$firstArgument instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($firstArgument, new \PHPStan\Type\ObjectType('Swift_Attachment'))) {
            return null;
        }
        if (!$this->isName($firstArgument->name, 'fromPath')) {
            return null;
        }
        $methodCall->name = new \PhpParser\Node\Identifier('attachFromPath');
        $methodCall->args = $this->nodeFactory->createArgs($firstArgument->args);
        return $methodCall;
    }
    private function refactorEmbedMethod(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $firstArgument = $methodCall->args[0]->value;
        if (!$firstArgument instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($firstArgument, new \PHPStan\Type\ObjectType('Swift_Image'))) {
            return null;
        }
        if (!$this->isName($firstArgument->name, 'fromPath')) {
            return null;
        }
        $methodCall->name = new \PhpParser\Node\Identifier('embedFromPath');
        $methodCall->args = $this->nodeFactory->createArgs($firstArgument->args);
        return $methodCall;
    }
}
