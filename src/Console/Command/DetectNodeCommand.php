<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202402\Nette\Utils\Strings;
use Rector\CustomRules\SimpleNodeDumper;
use Rector\PhpParser\Parser\SimplePhpParser;
use RectorPrefix202402\Symfony\Component\Console\Command\Command;
use RectorPrefix202402\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202402\Symfony\Component\Console\Input\InputOption;
use RectorPrefix202402\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202402\Symfony\Component\Console\Question\Question;
use RectorPrefix202402\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
final class DetectNodeCommand extends Command
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @var string
     * @see https://regex101.com/r/Fe8n73/1
     */
    private const CLASS_NAME_REGEX = '#(?<class_name>PhpParser(.*?))\\(#ms';
    /**
     * @var string
     * @see https://regex101.com/r/uQFuvL/1
     */
    private const PROPERTY_KEY_REGEX = '#(?<key>[\\w\\d]+)\\:#';
    public function __construct(SymfonyStyle $symfonyStyle, SimplePhpParser $simplePhpParser)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->simplePhpParser = $simplePhpParser;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('detect-node');
        $this->setDescription('Detects node for provided PHP content');
        $this->addOption('loop', null, InputOption::VALUE_NONE, 'Keep open so you can try multiple inputs');
        $this->setAliases(['dump-node']);
        // @todo invoke https://github.com/matthiasnoback/php-ast-inspector/
        // $this->addOption('file');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        if ((bool) $input->getOption('loop')) {
            while (\true) {
                $this->askQuestionAndDumpNode();
            }
        }
        $this->askQuestionAndDumpNode();
        return self::SUCCESS;
    }
    private function addConsoleColors(string $contents) : string
    {
        // decorate class names
        $colorContents = Strings::replace($contents, self::CLASS_NAME_REGEX, static function (array $match) : string {
            return '<fg=green>' . $match['class_name'] . '</>(';
        });
        // decorate keys
        return Strings::replace($colorContents, self::PROPERTY_KEY_REGEX, static function (array $match) : string {
            return '<fg=yellow>' . $match['key'] . '</>:';
        });
    }
    private function askQuestionAndDumpNode() : void
    {
        $question = new Question('Write short PHP code snippet');
        $phpContents = $this->symfonyStyle->askQuestion($question);
        try {
            $nodes = $this->simplePhpParser->parseString($phpContents);
        } catch (Throwable $exception) {
            $this->symfonyStyle->warning('Provide valid PHP code');
            return;
        }
        $dumpedNodesContents = SimpleNodeDumper::dump($nodes);
        // colorize
        $colorContents = $this->addConsoleColors($dumpedNodesContents);
        $this->symfonyStyle->writeln($colorContents);
        $this->symfonyStyle->newLine();
    }
}
