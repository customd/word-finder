<?php

namespace CustomD\WordFinder\CharacterMaps;

use CustomD\WordFinder\CharacterMap;

class Basic implements CharacterMap
{
    public function getRandomChar()
    {
        return chr(rand(65, 90));
    }
}
