<?php

namespace CustomD\WordFinder\Facades;

use Illuminate\Support\Facades\Facade;

/**
* @method static \CustomD\WordFinder\Grid create(Collection $wordList, ?int $gridSize = null, ?int $min_word_length = null, ?int $max_word_length = null)
*
* @see \CustomD\WordFinder\WordFinder
*/
class WordFinder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'word-finder';
    }
}
