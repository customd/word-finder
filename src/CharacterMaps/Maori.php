<?php

namespace CustomD\WordFinder\CharacterMaps;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use CustomD\WordFinder\CharacterMap;

class Maori implements CharacterMap
{

    protected Collection $chars;

    public function __construct()
    {
        $this->chars = collect([
            'a',
            'ā',
            'e',
            'ē',
            'g',
            'h',
            'i',
            'ī',
            'k',
            'm',
            'n',
            'o',
            'ō',
            'p',
            'r',
            't',
            'u',
            'ū',
            'w'
        ]);
    }

    public function getRandomChar()
    {

        return Str::upper($this->chars->random());
    }
}
