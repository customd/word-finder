<?php

namespace CustomD\WordFinder\CharacterMaps;

use Illuminate\Support\Str;
use CustomD\WordFinder\CharacterMap;

class Basic implements CharacterMap
{
    public function getRandomChar()
    {
        return Str::upper(chr(rand(65, 90)));
    }
}
