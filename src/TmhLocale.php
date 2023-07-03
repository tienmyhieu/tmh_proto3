<?php

class TmhLocale
{
    private $provider;
    private $locales = [];

    public function __construct(TmhProvider $provider)
    {
        $this->provider = $provider;
        $this->initialize();
    }

    public function get($key)
    {
        return array_key_exists($key, $this->locales) ? $this->locales[$key] : $key;
    }

    private function initialize()
    {
        $this->locales = $this->provider->locales();
    }

    public function translate($text)
    {
        if (preg_match('/(locales)(\.)(.+)/', $text, $matches)) {
            return $this->get($matches[3]);
        }
        return $text;
    }

    public function translateAll($parts): array
    {
        $translatedParts = [];
        if ($parts) {
            foreach ($parts as $part) {
                $translatedParts[] = $this->translate($part);
            }
        }
        return $translatedParts;
    }

    public function translateAllWithLocale($locale, $parts): array
    {
        $currentLocales = $this->locales;
        $this->locales = $this->provider->otherLocales($locale);
        $translated = $this->translateAll($parts);
        $this->locales = $currentLocales;
        return $translated;
    }
}