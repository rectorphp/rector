<?php declare(strict_types=1);

namespace Rector\ConsoleDiffer\Console\Formatter;

use Nette\Utils\Strings;
use Symfony\Component\Console\Formatter\OutputFormatter;
use function Safe\sprintf;

/**
 * Most is copy-pasted from https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/src/Differ/DiffConsoleFormatter.php
 * to be used as standalone class, without need to require whole package.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class DiffConsoleFormatter
{
    /**
     * @var string
     */
    private $template;

    public function __construct()
    {
        $this->template = sprintf('<comment>    ---------- begin diff ----------</comment>' .
        '%s%%s%s' .
        '<comment>    ----------- end diff -----------</comment>', PHP_EOL, PHP_EOL);
    }

    public function format(string $diff): string
    {
        return $this->formatWithTemplate($diff, $this->template);
    }

    private function formatWithTemplate(string $diff, string $template): string
    {
        return sprintf($template, implode(PHP_EOL, array_map(function ($string) {
            // make "+" lines green
            // make "-" lines red
            // make "@ note" lines cyan
            $string = preg_replace(
                ['/^(\+.*)/', '/^(\-.*)/', '/^(@.*)/'],
                ['<fg=green>\1</fg=green>', '<fg=red>\1</fg=red>', '<fg=cyan>\1</fg=cyan>'],
                $string
            );

            if ($string === ' ') {
                $string = rtrim($string);
            }

            return $string;
        }, Strings::split(OutputFormatter::escape(rtrim($diff)), "#\n\r|\n#"))));
    }
}
