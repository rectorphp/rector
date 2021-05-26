<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Yaml\Form\Rector;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/10.2/en-us/Changelog/10.0/Feature-80420-AllowMultipleRecipientsInEmailFinisher.html
 */
final class EmailFinisherRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface
{
    /**
     * @var string
     */
    private const FINISHERS = 'finishers';
    /**
     * @var string
     */
    private const OPTIONS = 'options';
    /**
     * @var string
     */
    private const RECIPIENT_ADDRESS = 'recipientAddress';
    /**
     * @var string
     */
    private const RECIPIENTS = 'recipients';
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @param mixed[] $yaml
     */
    public function refactor(array $yaml) : array
    {
        if (!\array_key_exists(self::FINISHERS, $yaml)) {
            return $yaml;
        }
        $applied = \false;
        foreach ($yaml[self::FINISHERS] as $finisherKey => $finisher) {
            if (!\array_key_exists('identifier', $finisher)) {
                continue;
            }
            if (!\in_array($finisher['identifier'], ['EmailToSender', 'EmailToReceiver'], \true)) {
                continue;
            }
            if (!\array_key_exists(self::OPTIONS, $finisher)) {
                continue;
            }
            $recipients = [];
            foreach ((array) $finisher[self::OPTIONS] as $optionKey => $optionValue) {
                if (!\in_array($optionKey, ['replyToAddress', 'carbonCopyAddress', 'blindCarbonCopyAddress', self::RECIPIENT_ADDRESS, 'recipientName'], \true)) {
                    continue;
                }
                if ('replyToAddress' === $optionKey) {
                    $yaml[self::FINISHERS][$finisherKey][self::OPTIONS]['replyToRecipients'][] = $optionValue;
                } elseif ('carbonCopyAddress' === $optionKey) {
                    $yaml[self::FINISHERS][$finisherKey][self::OPTIONS]['carbonCopyRecipients'][] = $optionValue;
                } elseif ('blindCarbonCopyAddress' === $optionKey) {
                    $yaml[self::FINISHERS][$finisherKey][self::OPTIONS]['blindCarbonCopyRecipients'][] = $optionValue;
                }
                unset($yaml[self::FINISHERS][$finisherKey][self::OPTIONS][$optionKey]);
            }
            if (isset($finisher[self::OPTIONS][self::RECIPIENT_ADDRESS])) {
                $recipients[$finisher[self::OPTIONS][self::RECIPIENT_ADDRESS]] = $finisher[self::OPTIONS]['recipientName'] ?: '';
            }
            if (isset($finisher[self::OPTIONS][self::RECIPIENTS])) {
                $yaml[self::FINISHERS][$finisherKey][self::OPTIONS][self::RECIPIENTS] = \array_merge($recipients, $yaml[self::FINISHERS][$finisherKey][self::OPTIONS][self::RECIPIENTS]);
            } else {
                $yaml[self::FINISHERS][$finisherKey][self::OPTIONS][self::RECIPIENTS] = $recipients;
            }
            $applied = \true;
        }
        $file = $this->currentFileProvider->getFile();
        if ($applied && $file instanceof \Rector\Core\ValueObject\Application\File) {
            // TODO: How to get the line number of the file?
            $file->addRectorClassWithLine(new \Rector\ChangesReporting\ValueObject\RectorWithLineChange($this, 0));
        }
        return $yaml;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert single recipient values to array for EmailFinisher', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
finishers:
  -
    options:
      recipientAddress: bar@domain.com
      recipientName: 'Bar'
CODE_SAMPLE
, <<<'CODE_SAMPLE'
finishers:
  -
    options:
      recipients:
        bar@domain.com: 'Bar'
CODE_SAMPLE
)]);
    }
}
