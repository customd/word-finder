<?php

namespace CustomD\WordFinder;

use RuntimeException;
use Illuminate\Support\Str;
use CustomD\WordFinder\Word;
use Illuminate\Support\Collection;
use CustomD\WordFinder\Traits\HasWordCollection;
use CustomD\WordFinder\Facades\WordFinder as WordFinderFacade;

class Grid
{

    use HasWordCollection;


    /**
     * minimum word length for the puzzle
     */
    protected int $minWordLen;

    /**
     * maximium word length for the puzzle
     */
    protected int $maxWordLen;

    /**
     * grid side length
     */
    protected int $gridSize;

    /**
     * cells holding the collection of lettes
     */
    protected array $cells = [];

    /**
     * Words used within the puzzle
     */
    protected array $wordsList = [];

    /**
     * column mapping
     */
    protected array $columnArray = [];

    public function __construct(int $gridSize, int $minWordLen, int $maxWordLen, Collection $wordsCollection)
    {
        $this->minWordLen = $minWordLen;
        $this->maxWordLen = $maxWordLen;

        $this->setGridsize($gridSize)
            ->setWordsCollection($wordsCollection)
            ->initGrid();
    }

    protected function setGridSize(int $gridSize): self
    {
        if ($gridSize < $this->minWordLen) {
            throw new RuntimeException('size must be greater than '.$this->minWordLen);
        }

        $this->gridSize = $gridSize;
        //max word length cannot be greater than the grid size.
        $this->maxWordLen = min($gridSize, $this->maxWordLen);

        return $this;
    }

    protected function initGrid(): self
    {
        $this->cells = array_fill(0, $this->gridSize * $this->gridSize, null);

        for ($i = 0; $i < (2 * $this->gridSize * $this->gridSize); $i++) {
            $this->columnArray[$i]=$this->getColumnDefault($i);
        }

        return $this;
    }

    public function generate(): self
    {
        $blocks = $this->gridSize * $this->gridSize; //36
        $i=rand(0, $blocks-1);

        $complete=0;                   //0
        while ($complete < $blocks) {
            $this->placeWord($i);
            $complete++;
            $i++;
            if ($i==$blocks) {
                $i=0;
            }
        }

        return $this;
    }

    protected function addPlacedWord(Word $word, int $increment, int $len): void
    {
        $string = '';
        $flag=false;

        for ($i=$word->getStart(); $i<=$word->getEnd(); $i+=$increment) {
            if ($this->cells[$i] === null) {
                $string .= '_';
            } else {
                $string .= $this->cells[$i];
                $flag=true;
            }
        }

        if (! $flag) {
            $randomWord = $this->getRandomWord($len);
            $word->setLabel($randomWord);
            $this->addWord($word);
            return;
        }

        if (Str::contains($string, '_')===false) {
            return;
        }

        $word->setInversed(false)->setLabel($this->getWordLike($string));
        $this->addWord($word);
    }

    protected function placeWordHorizontally(Word $word, int $len): void
    {
        $inc = 1;
        $word->setEnd($word->getStart()+$len-1);
        while ($this->columnArray[$word->getEnd()] < $this->columnArray[$word->getStart()]) {
            $word->setStart($word->getStart()-1);
            $word->setEnd($word->getStart()+$len-1);
        }

        $this->addPlacedWord($word, $inc, $len);
    }

    protected function placeWordVertical(Word $word, int $len): void
    {
        $inc=$this->gridSize;
        $word->setEnd($word->getStart()+($len*$this->gridSize)-$this->gridSize);
        while ($word->getEnd()>($this->gridSize*$this->gridSize)-1) {
            $word->setStart($word->getStart()-$this->gridSize);
            $word->setEnd($word->getStart()+($len*$this->gridSize)-$this->gridSize);
        }

        $this->addPlacedWord($word, $inc, $len);
    }

    protected function placeWordDiagonallyLtr(Word $word, int $len): void
    {
        $inc=$this->gridSize+1;
        $word->setEnd($word->getStart()+($len*($this->gridSize+1))-($this->gridSize+1));
        while ($this->columnArray[$word->getEnd()] < $this->columnArray[$word->getStart()]) {
            $word->setStart($word->getStart()-1);
            $word->setEnd($word->getStart()+($len*($this->gridSize+1))-($this->gridSize+1));
        }
        while ($word->getEnd()>($this->gridSize*$this->gridSize)-1) {
            $word->setStart($word->getStart()-$this->gridSize);
            $word->setEnd($word->getStart()+($len*($this->gridSize+1))-($this->gridSize+1));
        }
        $this->addPlacedWord($word, $inc, $len);
    }

    protected function placeWordDiagonallyRtl(Word $word, int $len): void
    {
        $inc=$this->gridSize-1;
        $word->setEnd($word->getStart()+(($len-1)*($this->gridSize-1)));
        while ($this->columnArray[$word->getEnd()] > $this->columnArray[$word->getStart()]) {
            $word->setStart($word->getStart()+1);
            $word->setEnd($word->getStart()+(($len-1)*($this->gridSize-1)));
        }
        while ($word->getEnd()>($this->gridSize*$this->gridSize)-1) {
            $word->setStart($word->getStart()-$this->gridSize);
            $word->setEnd($word->getStart()+(($len-1)*($this->gridSize-1)));
        }
        $this->addPlacedWord($word, $inc, $len);
    }

    protected function placeWord($start): void
    {
        $len = $this->getRandomWordLength();
        if ($len === 0) {
            return;
        }
        $word = Word::createRandom($start);

        switch ($word->getOrientation()) {
            case Word::HORIZONTAL:
                $this->placeWordHorizontally($word, $len);
                return;

            case Word::VERTICAL:
                $this->placeWordVertical($word, $len);
                return;

            case Word::DIAGONAL_LEFT_TO_RIGHT:
                $this->placeWordDiagonallyLtr($word, $len);
                return;

            case Word::DIAGONAL_RIGHT_TO_LEFT:
                $this->placeWordDiagonallyRtl($word, $len);
                return;
        }
    }

    protected function getColumnDefault(int $x): int
    {
        return ($x % $this->gridSize)+1;
    }

    protected function addWord(Word $word): void
    {
        if ($word->getLabel() === null) {
            return;
        }

        $j=0;
        $incrementBy = 1;
        switch ($word->getOrientation()) {
            case Word::HORIZONTAL:
                $incrementBy=1;
                break;

            case Word::VERTICAL:
                $incrementBy=$this->gridSize;
                break;

            case Word::DIAGONAL_LEFT_TO_RIGHT:
                $incrementBy=$this->gridSize+1;
                break;

            case Word::DIAGONAL_RIGHT_TO_LEFT:
                $incrementBy=$this->gridSize-1;
                break;
        }

        for ($i = $word->getStart(); $j < Str::length($word->getLabel()); $i += $incrementBy) {
            $nchar = Str::substr($word->getLabel(), $j, 1);
            if ($this->cells[$i] !== $nchar
                && ! is_null($this->cells[$i])) {
                throw new RuntimeException("Null or Char required: {$this->cells[$i]} - {$nchar}");
            }
            $this->cells[$i] = Str::substr($word->getLabel(), $j, 1);
            $j++;
        }

        $this->wordsList[]=$word;
    }

    public function getTextGrid()
    {
        $r = '';
        foreach ($this->getGrid() as $idx => $row) {
            if ($idx > 0) {
                $r .= "\n";
            }
            $r .= implode(" ", $row);
        }
        return $r;
    }

    public function getGrid()
    {

        $charClass = resolve(config('word-finder.character_map'));

        $callback = method_exists($charClass, 'unmapChars') ? fn($str) => $charClass->unmapChars($str) : fn($str) => $str;

        $return = [];
        $column = 0;
        $row = 0;
        foreach ($this->cells as $letter) {
            $cell = $letter ?? WordFinderFacade::getRandomChar();
            $return[$row][$column] = $callback($cell);
            $column++;
            if ($column === $this->gridSize) {
                $row++;
                $column = 0;
            }
        }

        return $return;
    }

    public function getPuzzleWords()
    {
        return collect($this->wordsList)->map(function (Word $word) {
            $str = $word->getLabel(true);
             $charClass = resolve(config('word-finder.character_map'));

            return method_exists($charClass, 'unmapChars') ? $charClass->unmapChars($str) : $str;
        });
    }

    public function getGridSize()
    {
        return $this->gridSize;
    }
}
