<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RectorGuess;

final class RectorGuessFilter
{
    /**
     * @param RectorGuess[] $rectorGuesses
     * @return RectorGuess[]
     */
    public function filterRectorGuessesToShow(array $rectorGuesses): array
    {
        $rectorGuesses = $this->filterOutUsefulGuessedRectors($rectorGuesses);

        return $this->filterOutDuplicatedGuessedRectors($rectorGuesses);
    }

    /**
     * @param RectorGuess[] $rectorGuesses
     * @return RectorGuess[]
     */
    private function filterOutUsefulGuessedRectors(array $rectorGuesses): array
    {
        return array_filter($rectorGuesses, function (RectorGuess $rectorGuess) {
            return $rectorGuess->isUseful();
        });
    }

    /**
     * @param RectorGuess[] $rectorGuesses
     * @return RectorGuess[]
     */
    private function filterOutDuplicatedGuessedRectors(array $rectorGuesses): array
    {
        $allMessages = [];
        foreach ($rectorGuesses as $rectorGuess) {
            $allMessages[] = $rectorGuess->getMessage();
        }

        $filteredGuessedRectors = [];
        foreach ($rectorGuesses as $rectorGuess) {
            foreach ($allMessages as $message) {
                // experimental; maybe count from message length?
                $levenshtein = levenshtein($rectorGuess->getMessage(), $message);
                if ($levenshtein !== 0 && $levenshtein < 10) {
                    continue 2;
                }
            }

            $filteredGuessedRectors[] = $rectorGuess;
        }

        return $filteredGuessedRectors;
    }
}
