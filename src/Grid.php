<?php

namespace CustomD\WordFinder;

use Illuminate\Support\Collection;
use RuntimeException;

class Grid
{

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


    protected Collection $wordsCollection; // base de données de mots SQLite

    const RENDER_HTML = 0; // afficher la grille en HTML
    const RENDER_TEXT = 1; // afficher la grille en mode texte

    protected $cells; // (tableau de size*size éléments String) cellules de la grille, chacune contenant une lettre
    protected $wordsList = []; // (tableau d'objets Word) : liste des mots à trouver
    protected $columnArray = []; // tableau (Int) des numéros des colonnes d'après les index des cellules

    public function __construct(int $gridSize, int $minWordLen, int $maxWordLen, Collection $wordsCollection)
    {
        $this->minWordLen = $minWordLen;
        $this->maxWordLen = $maxWordLen;

        $this->setWordsCollection($wordsCollection)
            ->setGridsize($gridSize)
            ->initGrid();
    }

    protected function setGridSize(int $gridSize): self
    {
        if ($gridSize < $this->minWordLen) {
            throw new RuntimeException('size must be greater than '.$this->minWordLen);
        }

        if ($gridSize < $this->maxWordLen) {
            $this->maxWordLen = $gridSize;
        }

        $this->gridSize = $gridSize;

        return $this;
    }

    protected function setWordsCollection(Collection $wordsCollection): self
    {
        $this->wordsCollection = $wordsCollection->filter(function ($word) {
            return strlen($word) >= $this->minWordLen && strlen($word) <= $this->maxWordLen;
        })->map(fn($word) => strtoupper($word));

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
        $blocks = $this->gridSize * $this->gridSize;
        $i=rand(0, $blocks-1);

        $complete=0;
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

    protected function getRandomWordLength(array $exclude = []): int
    {
        if (count($exclude) >= ($this->maxWordLen - $this->minWordLen)) {
            throw new RuntimeException("Failed to generate a starting word . please add some additional words to your system");
        }

        do {
            $len = rand($this->minWordLen, $this->gridSize);
        } while (in_array($len, $exclude));

        $available = $this->wordsCollection->filter(function ($word) use ($len) {
            return strlen($word) === $len;
        })->count();

        $exclude[] = $len;

        return $available > 0 ? $len : $this->getRandomWordLength($exclude);
    }

    protected function addPlacedWord(Word $word, int $increment, int $len): void
    {
        $string = '';
        $flag=false;

        for ($i=$word->getStart(); $i<=$word->getEnd(); $i+=$increment) {
            if ($this->cells[$i]=='') {
                $string .= '_';
            } else {
                $string .= $this->cells[$i];
                $flag=true;
            }
        }

        if (! $flag) {
            $randomWord = $this->getRandomWord($len);
            $word->setLabel($word->getInversed() ? strrev($randomWord) : $randomWord);
            $this->addWord($word);
            return;
        }

        if (strpos($string, '_')===false) {
            return;
        }

        $word->setLabel($this->getWordLike($string));
        $word->setInversed(false);
        $this->addWord($word);
    }

    protected function placeWordHorizontally(Word $word, int $len): void
    {
        $inc = 1;
        $word->setEnd($word->getStart()+$len-1);
                 // si mot placé sur 2 lignes on décale à gauche
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
                // si le mot dépasse la grille en bas, on décale vers le haut
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
                // si le mot dépasse la grille à droite, on décale à gauche
        while ($this->columnArray[$word->getEnd()] < $this->columnArray[$word->getStart()]) {
            $word->setStart($word->getStart()-1);
            $word->setEnd($word->getStart()+($len*($this->gridSize+1))-($this->gridSize+1));
        }
                // si le mot dépasse la grille en bas, on décale vers le haut
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
                // si le mot sort de la grille à gauche, on décale à droite
        while ($this->columnArray[$word->getEnd()] > $this->columnArray[$word->getStart()]) {
            $word->setStart($word->getStart()+1);
            $word->setEnd($word->getStart()+(($len-1)*($this->gridSize-1)));
        }
                // si le mot dépasse la grille en bas, on décale vers le haut
        while ($word->getEnd()>($this->gridSize*$this->gridSize)-1) {
            $word->setStart($word->getStart()-$this->gridSize);
            $word->setEnd($word->getStart()+(($len-1)*($this->gridSize-1)));
        }
        $this->addPlacedWord($word, $inc, $len);
    }

    protected function placeWord($start): void
    {
        $len = $this->getRandomWordLength();
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


    protected function addWord(Word $word): void
    {
        if ($word->getLabel() === null) {
            return;
        }

        $j=0;
        switch ($word->getOrientation()) {
            case Word::HORIZONTAL:
                for ($i=$word->getStart(); $j<strlen($word->getLabel()); $i++) {
                    $this->cells[$i]=substr($word->getLabel(), $j, 1);
                    $j++;
                }
                break;

            case Word::VERTICAL:
                for ($i=$word->getStart(); $j<strlen($word->getLabel()); $i+=$this->gridSize) {
                    $this->cells[$i]=substr($word->getLabel(), $j, 1);
                    $j++;
                }
                break;

            case Word::DIAGONAL_LEFT_TO_RIGHT:
                for ($i=$word->getStart(); $j<strlen($word->getLabel()); $i+=$this->gridSize+1) {
                    $this->cells[$i]=substr($word->getLabel(), $j, 1);
                    $j++;
                }
                break;

            case Word::DIAGONAL_RIGHT_TO_LEFT:
                for ($i=$word->getStart(); $j<strlen($word->getLabel()); $i+=$this->gridSize-1) {
                    $this->cells[$i]=substr($word->getLabel(), $j, 1);
                    $j++;
                }
                break;
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
        $return = [];
        $column = 0;
        $row = 0;
        foreach ($this->cells as $letter) {
            $cell = $letter ?? chr(rand(65, 90));
            $return[$row][$column] = $cell;
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
            $label = $word->getLabel();
            if ($word->getInversed()) {
                $label = strrev(/** @scrutinizer ignore-type */$label);
            }
            return $label;
        });
    }
}
