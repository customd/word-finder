<?php

namespace CustomD\WordFinder\CharacterMaps;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use CustomD\WordFinder\CharacterMap;

class Maori implements CharacterMap
{

    protected Collection $chars;

    public array $mappedChars = [
        'ā' => '1',
        'ē' => '2',
        'ī' => '3',
        'ō' => '4',
        'ū' => '5',
    ];

    public function __construct()
    {
        $this->chars = collect([
            'a',
            '1',
            'e',
            '2',
            'g',
            'h',
            'i',
            '3',
            'k',
            'm',
            'n',
            'o',
            '4',
            'p',
            'r',
            't',
            'u',
            '5',
            'w'
        ]);
    }


    public function getRandomChar()
    {

        return Str::upper($this->chars->random());
    }


    public function mapChars($word)
    {
        return Str::replace(array_keys($this->mappedChars), $this->mappedChars, Str::lower($word));
    }

    public function unmapChars($word)
    {
        $keys = collect($this->mappedChars)->mapWithKeys(function ($val, $key) {
            return [Str::upper($key) => Str::upper($val)];
        })->toArray();

        return Str::replace($keys, array_keys($keys), Str::upper($word));
    }
}
