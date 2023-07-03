<?php

class TmhProvider
{
    private $currentYear;
    private $data;
    private $json;
    public function __construct(TmhJson $json)
    {
        $this->currentYear = date('Y');
        $this->data = [];
        $this->json = $json;
        $this->initialize();
    }

    public function ancestorRoutes(): array
    {
        return array_map(function($ancestor) { return $ancestor['route']; }, $this->data['ancestors']);
    }

    public function ancestors()
    {
        return $this->data['ancestors'];
    }

    public function attributes()
    {
        return $this->data['attributes'];
    }

    public function catalog($uuid)
    {
        $catalogExists = array_key_exists($uuid, $this->data['catalogs']);
        return $catalogExists ? $this->data['catalogs'][$uuid] : [];
    }

    public function components()
    {
        return $this->data['entityTypeTemplate']['components'];
    }

    public function currentYear()
    {
        return $this->currentYear;
    }

    public function descendants()
    {
        return $this->data['descendants'];
    }

    public function descendantRoutes(): array
    {
        return array_map(function($descendant) {
            return [
                'active' => $descendant['active'],
                'href' => $this->data['baseRoute'] . '/' . $descendant['route'],
                'innerHtml' => $descendant['entity'],
                'title' => $descendant['entity']
            ];
        }, array_filter($this->data['descendants'], function ($descendant) {
            return 0 < strlen($descendant['route']);
        }));
    }

    public function entityTemplate()
    {
        return $this->data['entityTemplate'];
    }

    public function entityTypeTemplate()
    {
        return $this->data['entityTypeTemplate'];
    }

    public function firstAncestor(): array
    {
        return [
            TMH_HOME => [
                'route' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'],
                'descendant' => '',
                'entity' => TMH_HOME,
                'template' => TMH_HOME
            ]
        ];
    }

    public function language()
    {
        return substr($this->data['locale'], 0, 2);
    }

    public function locale()
    {
        return $this->data['locale'];
    }

    public function locales()
    {
        return $this->data['locales'];
    }

    public function otherDomains(): array
    {
        $otherDomains = [];
        $domains = array_unique(array_values($this->domains()));
        $domainMap = array_flip($this->domains());
        foreach ($domains as $locale) {
            if ($locale != $this->data['locale']) {
                $localeKey = 'locales.language_' . strtolower(str_replace("-", "_", $locale));
                $languageCode =  substr($locale, 0, 2) == 'zh' ? $locale : substr($locale, 0, 2);
                $otherDomains[$locale] = [
                    'domain' => $_SERVER['REQUEST_SCHEME'] . '://' . $domainMap[$locale],
                    'hreflang' => $languageCode,
                    'innerHtml' => $this->nativeName($locale),
                    'title_prefix' => $localeKey
                ];
            }
        }
        return $otherDomains;
    }

    public function otherLocales($locale): array
    {
        $locales = [];
        foreach ($this->data['entityTypeTemplate']['locales'] as $category) {
            $locales = array_merge($locales, $this->json->locale(__DIR__ . '/locales/' .$locale . '/',  $category));
        }
        return $locales;
    }

    private function directoryParts($locale)
    {
        $otherBaseDirectory = str_replace($this->data['locale'], $locale, $this->data['baseDirectory']);
        $directoryParts = explode('/', $otherBaseDirectory);
        unset($directoryParts[0]);
        unset($directoryParts[count($directoryParts)]);
        return $directoryParts;
    }

    private function otherLocaleRoutes($locale): array
    {
        $path = '';
        $otherLocaleRoutes = [];
        foreach ($this->directoryParts($locale) as $directoryPart) {
            $path .= $directoryPart . '/';
            $otherLocaleRoutes[] = $path;
        }
        unset($otherLocaleRoutes[0]);
        return $otherLocaleRoutes;
    }

    private function ancestorEntities(): array
    {
        $entities = [];
        $ancestors = $this->ancestors();
        foreach ($ancestors as $ancestor) {
            $entities[] = $ancestor['entity'];
        }
        return $entities;
    }

    public function otherRoute($locale): string
    {
        $otherRoute = $this->data['mimeType'] != 'html' ? $this->data['mimeType'] . '/' : '';
        foreach ($this->otherLocaleRoutes($locale) as $localeRoute) {
            $routes = $this->json->routes(__DIR__ . '/' . $localeRoute);
            foreach ($routes as $route) {
//                if (!is_array($route)) {
//                    echo "<pre>";
//                    print_r($routes);
//                    echo $route . PHP_EOL;
//                    echo "</pre>";
//                }
                if (in_array($route['entity'], $this->ancestorEntities())) {
                    if ($route['route']) {
                        $otherRoute .= $route['route'] . '/';
                    }
                }
            }
        }
        return substr($otherRoute, 0, -1);
    }

    public function route()
    {
        return $this->data['route'];
    }

    public function routeGroups()
    {
        return $this->data['entityTemplate']['route_groups'];
    }

    public function routeSubGroups()
    {
        return $this->data['entityTemplate']['route_sub_groups'];
    }

    public function type()
    {
        return $this->data['route']['type'];
    }

    private function domains(): array
    {
        return  [
            'en.' . TMH_DOMAIN => 'en-GB', 'fr.' . TMH_DOMAIN => 'fr-FR', 'ja.' . TMH_DOMAIN => 'ja-JP',
            TMH_DOMAIN => 'vi-VN', 'www.' . TMH_DOMAIN => 'vi-VN', 'vi.' . TMH_DOMAIN => 'vi-VN',
            'zh-hans.' . TMH_DOMAIN => 'zh-Hans', 'zh-hant.' . TMH_DOMAIN => 'zh-Hant'
        ];
    }

    private function faultTolerance()
    {
        if (!$this->data['route']) {
            foreach ($this->data['descendants'] as $descendant) {
                if (0 == strlen($descendant['route'])) {
                    $this->data['route'] = $descendant;
                }
            }
        }
    }

    private function initialize()
    {
        $this->setLocale();
        $this->setRoute();
        $this->faultTolerance();
        $this->setBaseRoute();
        $this->setDescendants();
        $this->setLocales();
        $this->setTemplate();
        $this->setAttributes();
        $this->setCatalogs();
    }

    private function nativeName($locale): string
    {
        $nativeNames = [
            'en-GB' => 'English', 'fr-FR' => 'Français', 'ja-JP' => '日本語', 'vi-VN' => 'Tiếng Việt',
            'zh-Hans' => '中文 (简体)', 'zh-Hant' => '中文 (繁体)'
        ];
        $nativeNameExists = array_key_exists($locale, $nativeNames);
        return $nativeNameExists ? $nativeNames[$locale] : 'Tiếng Việt';
    }

    private function routeSegments()
    {
        parse_str($_SERVER['REDIRECT_QUERY_STRING'], $fields);
        $this->data['mimeType'] = 'html';
        $routeSegments = explode("/", $fields['title']);
        if (in_array($routeSegments[0], ['pdf', 'api'])) {
            $this->data['mimeType'] = $routeSegments[0];
            unset($routeSegments[0]);
        }
        return $routeSegments;
    }

    private function routes()
    {
        $routes = $this->json->routes(__DIR__ . $this->data['baseDirectory']);
        if (array_key_exists('common', $routes)) {
            $path = __DIR__ . '/routes/' . $this->data['locale'] . $routes['common'];
            $routes = $this->json->routes($path);
        }
        return $routes;
    }

    private function setAttributes()
    {
        $path = __DIR__ . '/attribute';
        foreach (scandir($path) as $file) {
            if (!in_array($file ,array(".", ".."))) {
                $attribute = str_replace('.json', '', $file);
                $this->data['attributes'][$attribute] = $this->json->attribute($path . '/', $attribute);
            }
        }
    }

    private function setBaseRoute()
    {
        $baseRoute = '/';
        if ($this->data['ancestors']) {
            $baseRoute .= implode('/', $this->ancestorRoutes());
        }
        $this->data['baseRoute'] = $baseRoute == '/' ? '' : $baseRoute;
    }

    private function setCatalogs()
    {
        $path = __DIR__ . '/catalog';
        foreach (scandir($path) as $file) {
            if (!in_array($file ,array(".", ".."))) {
                $catalog = str_replace('.json', '', $file);
                $this->data['catalogs'][$catalog] = $this->json->catalog($path . '/', $catalog);
            }
        }
    }

    private function setDescendants()
    {
        if ($this->data['route']) {
            if ($this->data['route']['descendant']) {
                $this->data['descendants'] = $this->routes();
            }
        }
    }

    private function setLocale()
    {
        $domains = $this->domains();
        $domainExists = array_key_exists($_SERVER['SERVER_NAME'], $domains);
        $this->data['locale'] = $domainExists ? $domains[$_SERVER['SERVER_NAME']] : $domains[TMH_DOMAIN];
    }

    private function setLocales()
    {
        if ($this->data['route']) {
            $this->data['entityTypeTemplate'] = $this->json->template(
                __DIR__ . '/template/', $this->data['route']['type']
            );
            $this->data['locales'] = [];
            foreach ($this->data['entityTypeTemplate']['locales'] as $locale) {
                $locales = $this->json->locale(__DIR__ . '/locales/' . $this->data['locale'] . '/',  $locale);
                $this->data['locales'] = array_merge($this->data['locales'], $locales);
            }
        }
    }

    private function setRoute()
    {
        $this->data['baseDirectory'] = '/routes/' . $this->data['locale'] . '/';
        $this->data['route'] = [];
        $this->data['ancestors'] = [];
        foreach ($this->routeSegments() as $routeSegment) {
            $routes = $this->routes();
            $this->data['descendants'] = $routes;
            foreach ($routes as $route) {
                if ($route['route'] == $routeSegment) {
                    $this->data['route'] = $route;
                    $this->data['ancestors'][$route['entity']] = $route;
                    if ($route['descendant']) {
                        $this->data['baseDirectory'] .= $route['descendant'] . '/';
                        $this->data['ancestors'][$route['entity']]['baseDirectory'] = $this->data['baseDirectory'];
                    }
                }
            }
        }
    }

    private function setTemplate()
    {
        if ($this->data['route']) {
            $this->data['entityTemplate'] = $this->json->template(
                __DIR__ . '/template/', $this->data['route']['template']
            );
        }
    }
}