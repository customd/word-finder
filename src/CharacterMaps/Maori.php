<?php

namespace CustomD\WordFinder\CharacterMaps;

use Illuminate\Support\Collection;
use CustomD\WordFinder\CharacterMap;

class Moari implements CharacterMap
{

    protected Collection $chars;

    public function __construct()
    {
        $this->chars = collect([
            65,
            196,
            69,
            203,
            71,
            72,
            73,
            207,
            75,
            77,
            78,
            79,
            214,
            80,
            82,
            84,
            85,
            220,
            87,
        ]);
    }

    public function getRandomChar()
    {
        return $this->chars->random();
    }
}
