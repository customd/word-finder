<?php

namespace CustomD\WordFinder;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class WordFinder
{

    protected array $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function create(Collection $wordList, ?int $gridSize = null, ?int $min_word_length = null, ?int $max_word_length = null): Grid
    {
        return (new Grid(
            $gridSize ?? $this->config['default_grid_size'],
            $min_word_length ?? $this->config['default_min_word_length'],
            $max_word_length ?? $this->config['default_max_word_length'],
            $wordList
        ))->generate();
    }
}
