<?php

namespace Presta\ImageBundle\Model;

class AspectRatio
{
    private $value;
    private $label;
    private $checked;

    public function __construct(?float $value, string $label, bool $checked = false)
    {
        $this->value = $value;
        $this->label = $label;
        $this->checked = $checked;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }
}
