<?php
namespace CustomD\WordFinder;

use RuntimeException;

class Word
{

    public const HORIZONTAL = 0;
    public const VERTICAL = 1;
    public const DIAGONAL_LEFT_TO_RIGHT = 2;
    public const DIAGONAL_RIGHT_TO_LEFT = 3;

    protected int $start = 0;
    protected int $end = -1;
    protected int $orientation = 0;
    protected ?string $label;
    protected bool $inversed = false;

    public function __construct($start, $end, int $orientation = 0, ?string $label = null, bool $inversed = false)
    {
        $this->setStart($start)
            ->setEnd($end)
            ->setOrientation($orientation)
            ->setLabel($label)
            ->setinversed($inversed);
    }

    public static function createRandom(?int $start, ?string $label = null): self
    {
        return new self($start, -1, rand(0, 3), $label, rand(0, 1) === 1);
    }

    public function setStart(int $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function setEnd($end): self
    {
        $this->end = $end;
        return $this;
    }

    public function setOrientation(int $orientation): self
    {
        if (! in_array($orientation, [0,1,2,3])) {
            throw new RuntimeException("Orientation not valid");
        }
        $this->orientation = $orientation;
        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function setInversed(bool $inversed): self
    {
        $this->inversed = $inversed;
        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getOrientation(): int
    {
        return $this->orientation;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getInversed(): bool
    {
        return $this->inversed;
    }
}
