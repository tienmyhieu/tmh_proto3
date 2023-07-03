<?php

class TmhElement
{
    public function a($attributes, $innerHtml): array
    {
        return array_merge($this->baseElement(), ['element' => 'a', 'attributes' => $attributes, 'innerHtml' => $innerHtml]);
    }

    protected function baseElement(): array
    {
        return ['element' => '', 'attributes' => [], 'elements' => [], 'innerHtml' => '', 'selfClosing' => false, 'tabs' => '0'];
    }

    public function body($elements): array
    {
        return array_merge($this->baseElement(), ['element' => 'body', 'attributes' => ['class' => 'tmh_body'], 'elements' => $elements]);
    }

    public function br(): array
    {
        return array_merge($this->baseElement(), ['element' => 'br', 'selfClosing' => true]);
    }

    public function div($class, $elements, $innerHtml): array
    {
        $attributes = $class ? ['class' => $class] : [];
        return array_merge($this->baseElement(), ['element' => 'div', 'attributes' => $attributes, 'elements' => $elements, 'innerHtml' => $innerHtml]);
    }

    public function docType(): array
    {
        return array_merge($this->baseElement(), ['element' => '!DOCTYPE', 'selfClosing' => true]);
    }

    public function head($elements): array
    {
        return array_merge($this->baseElement(), ['element' => 'head', 'elements' => $elements]);
    }

    public function html($attributes, $elements): array
    {
        return array_merge($this->baseElement(), ['element' => 'html', 'attributes' => $attributes, 'elements' => $elements]);
    }

    public function span($class, $elements, $innerHtml): array
    {
        $attributes = $class ? ['class' => $class] : [];
        return array_merge($this->baseElement(), ['element' => 'span', 'attributes' => $attributes, 'elements' => $elements, 'innerHtml' => $innerHtml]);
    }

    public function tmhA($attributes, $innerHtml): array
    {
        return $this->a(array_merge(['class' => 'tmh_a'], $attributes), $innerHtml);
    }

    public function tmhContent($elements): array
    {
        return array_merge($this->baseElement(), ['element' => 'div', 'attributes' => ['class' => 'tmh_content'], 'elements' => $elements]);
    }

    public function link($attributes): array
    {
        return array_merge($this->baseElement(), ['element' => 'link', 'attributes' => $attributes, 'selfClosing' => true]);
    }

    public function meta($attributes): array
    {
        return array_merge($this->baseElement(), ['element' => 'meta', 'attributes' => $attributes, 'selfClosing' => true]);
    }

    public function title($innerHtml): array
    {
        return array_merge($this->baseElement(), ['element' => 'title', 'innerHtml' => $innerHtml]);
    }
}