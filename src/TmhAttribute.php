<?php

class TmhAttribute
{
    private $attributes;
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function contentDescription($uuid)
    {
        return $this->attributeByUuid('contentDescription', $uuid);
    }

    public function contentTitle($uuid)
    {
        return $this->attributeByUuid('contentTitle', $uuid);
    }

    public function innerHtml($uuid)
    {
        return $this->attributeByUuid('innerHtml', $uuid);
    }

    public function metaDescription($uuid)
    {
        return $this->attributeByUuid('metaDescription', $uuid);
    }

    public function metaKeywords($uuid)
    {
        return $this->attributeByUuid('metaKeywords', $uuid);
    }

    public function title($uuid)
    {
        return $this->attributeByUuid('title', $uuid);
    }

    private function attributeByUuid($attribute, $uuid)
    {
        $exists = array_key_exists($uuid, $this->attributes[$attribute]);
        return $exists ? $this->attributes[$attribute][$uuid] : $uuid;
    }
}