<?php

class TmhRender
{
    private $component;
    private $elementTree;

    public function __construct(TmhComponent $component, TmhElementTree $elementTree)
    {
        $this->component = $component;
        $this->elementTree = $elementTree;
    }

    public function render()
    {
        echo $this->elements($this->elementTree->getElementTree());
    }

    private function attributes($attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $replacement = $this->component->replace($value);
            $html .= ' ' . $key . '="' . $replacement . '"';
        }
        return $html;
    }

    public function childElements($element, $eol=PHP_EOL): string
    {
        $closingHtml = $element['selfClosing'] ? '' : '>';
        if (array_key_exists('class', $element['attributes'])) {
            if ($element['attributes']['class'] == 'tmh_title') {
                echo 'yes';
            }
        }
        return $element['elements'] ? '>' . PHP_EOL . $this->elements($element['elements']) : $closingHtml;
    }

    private function closeElement($element): string
    {
        //$eol = in_array($element['name'], ['a', 'img']) ? '' : PHP_EOL;
        $selfClosing = $element['selfClosing'];
        $isDocType = $element['element'] == '!DOCTYPE';
        $slash = $selfClosing && $isDocType ? ' html' : '/';
        return ($element['selfClosing'] ? $slash . '>' : '</' . $element['element']. '>') . PHP_EOL;
    }

    private function elements($elements): string
    {
        $html = '';
        foreach ($elements as $element) {
            $html .= $this->openElement($element);
            $html .= $this->innerHtml($element);
            $html .= $this->closeElement($element);
        }
        return $html;
    }

    public function innerHtml($element): string
    {
        $replacement = $this->component->replace($element['innerHtml']);
        return strlen($element['innerHtml']) > 0 ? '>' . $replacement : $this->childElements($element, '');
    }

    private function openElement($element): string
    {
        return '<' . $element['element'] . $this->attributes($element['attributes']);
    }
}