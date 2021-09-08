<?php

namespace CustomD\WordFinder\Traits;

use RuntimeException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasWordCollection
{

    /**
     * Database of words to choose from
     */
    protected Collection $wordsCollection;

    protected Collection $wordLengths;


    protected function setWordsCollection(Collection $wordsCollection): self
    {
        $this->wordsCollection = $wordsCollection
            ->map(
                function ($word) {
                    $charClass = resolve(config('word-finder.character_map'));
                    $word = (method_exists($charClass, 'mapChars')) ? $charClass->mapChars($word) : $word;
                    return Str::replace(" ", "", Str::upper($word));
                }
            )
            ->filter(
                fn ($word) =>  Str::length($word) >= $this->minWordLen && Str::length($word) <= $this->maxWordLen
            );

        $this->wordLengths = $this->wordsCollection->mapToGroups(fn($word) => [Str::length($word) => $word]);

        return $this;
    }

    protected function getRandomWordLength(array $exclude = []): int
    {

        if (count($exclude) === $this->wordLengths->keys()->count()) {
            return 0;
        }

        do {
            $len = $this->wordLengths->keys()->random();
        } while (in_array($len, $exclude));

        $available = $this->wordsCollection->filter(fn ($word) => Str::length($word) === $len)->count();

        if ($available > 0) {
            return $len;
        }

        $exclude[] = $len;

        return $this->getRandomWordLength($exclude);
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
            return Str::length($word) === $len;
        })->random(1)->first();

        $this->markWordUsed($word);

        return $word;
    }

    protected function getWordLike(string $pattern): ?string
    {
        $pattern = Str::replace("_", ".", $pattern);
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
