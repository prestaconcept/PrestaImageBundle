<?php

namespace Presta\ImageBundle\Model;

class AspectRatio
{
    private ?float $value;
    private string $label;
    private bool $checked;

    public function __construct(?float $value, string $label, bool $checked = false)
    {
        $this->value = $value;
        $this->label = $label;
        $this->checked = $checked;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @codeCoverageIgnore
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }
}
