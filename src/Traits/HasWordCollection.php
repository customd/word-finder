<?php

namespace CustomD\WordFinder\Traits;

use RuntimeException;
use Illuminate\Support\Collection;

trait HasWordCollection
{

    /**
     * Database of words to choose from
     */
    protected Collection $wordsCollection;


    protected function setWordsCollection(Collection $wordsCollection): self
    {
        $this->wordsCollection = $wordsCollection->filter(
            fn ($word) =>  strlen($word) >= $this->minWordLen && strlen($word) <= $this->maxWordLen
        )
        ->map(fn($word) => strtoupper($word));

        return $this;
    }

    protected function getRandomWordLength(array $exclude = []): int
    {
        if (count($exclude) >= ($this->maxWordLen - $this->minWordLen)) {
            throw new RuntimeException("Failed to generate a starting word . please add some additional words to your system");
        }

        do {
            $len = rand($this->minWordLen, $this->gridSize);
        } while (in_array($len, $exclude));

        $available = $this->wordsCollection->filter(fn ($word) => strlen($word) === $len)->count();

        $exclude[] = $len;

        return $available > 0 ? $len : $this->getRandomWordLength($exclude);
    }

    protected function markWordUsed($word): void
    {
        $this->wordsCollection = $this->wordsCollection->reject(function ($current) use ($word) {
            return $current === $word;
        });
    }

    protected function getRandomWord(int $len): string
    {
        $word = $this->wordsCollection->filter(function ($word) use ($len) {
            return strlen($word) === $len;
        })->random(1)->first();

        $this->markWordUsed($word);

        return $word;
    }

    protected function getWordLike(string $pattern): ?string
    {
        $pattern = str_replace("_", ".", $pattern);
        $words = $this->wordsCollection->filter(function ($word) use ($pattern) {
            return preg_match("/^$pattern\$/i", $word);
        });

        if ($words->count() === 0) {
            return null;
        }

        $word = $words->random(1)->first();

        $this->markWordUsed($word);
        return $word;
    }
}
