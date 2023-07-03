<?php

class TmhComponent
{
    private $attribute;
    private $components;
    private $locale;
    private $provider;
    private $templateTypes = ['emperor_coin', 'specimen'];
    private $uuid;
    public function __construct(TmhAttribute $attribute, TmhLocale $locale, TmhProvider $provider)
    {
        $this->attribute = $attribute;
        $this->locale = $locale;
        $this->provider = $provider;
        $route = $this->provider->route();
        $this->uuid = $route['entity'];
    }

    public function components(): array
    {
        $components = $this->provider->components();
        $delegates = [
            'contentDescription' =>  $this->contentDescription(),
            'contentTitle' => $this->contentTitle(),
            'languages' => $this->languages(),
            'navigation' => $this->navigation(),
            'routeGroups' => $this->routeGroups()
        ];
        foreach ($components as $component) {
            $this->components[$component] = $delegates[$component];
        }
        return $this->components;
    }

    public function contentDescription(): string
    {
        $contentDescription = '';
        if ($this->provider->type() == 'home') {
            $contentDescription = $this->translateAll($this->attribute->contentDescription($this->uuid), ': ');
        }
        return $contentDescription;
    }

    public function contentTitle(): string
    {
        return $this->translateAll($this->attribute->contentTitle($this->uuid), ': ');
    }

    public function languages(): array
    {
        $otherDomains = [];
        $route = $this->provider->route();
        $entityTitle = $this->attribute->title($route['entity']);
        foreach ($this->provider->otherDomains() as $locale => $otherDomain) {
            $otherRoute = $this->provider->otherRoute($locale);
            $otherRoute = $otherRoute == '/' ? $otherRoute : '/' . $otherRoute;
            $title = implode(' ', $this->locale->translateAllWithLocale($locale, $entityTitle));
            $titlePrefix = $this->locale->translate($otherDomain['title_prefix']);
            $otherDomains[] = [
                'href' => $otherDomain['domain'] . $otherRoute,
                'hreflang' => $otherDomain['hreflang'],
                'title' => $titlePrefix . ' - ' . $title,
                'innerHtml' => $otherDomain['innerHtml']
            ];
        }
        return $otherDomains;
    }

    public function navigation(): array
    {
        $navigation = [];
        $entity = $this->provider->route();
        if ($entity['entity'] != TMH_HOME) {
            $ancestors = $this->provider->ancestors();
            $firstAncestor = $this->provider->firstAncestor();
            $uuid = $firstAncestor[TMH_HOME]['entity'];
            $navigation[] = [
                'href' => $firstAncestor[TMH_HOME]['route'],
                'title' => $this->translateAll($this->attribute->title($uuid), ' '),
                'innerHtml' => $this->translateAll($this->attribute->innerHtml($uuid), ' ')
            ];
            foreach ($ancestors as $uuid => $ancestor) {
                $navigation[] = [
                    'href' => $ancestor['route'],
                    'title' => $this->translateAll($this->attribute->title($uuid), ' '),
                    'innerHtml' => $this->translateAll($this->attribute->innerHtml($uuid), ' ')
                ];
            }
        }
        return $navigation;
    }

    public function replace($attribute)
    {
        $patterns = [
            '__CDN__',
            'domain.language',
            '__IMAGES__',
            'meta.title',
            'meta.description',
            'meta.keywords',
            'route.route',
            '__CURRENT_YEAR__'
        ];
        $entity = $this->provider->route();
        $replacements = [
            TMH_CDN,
            $this->provider->language(),
            TMH_IMAGES,
            $this->translateAll($this->attribute->title($this->uuid), ' '),
            $this->translateAll($this->attribute->metaDescription($this->uuid), ' '),
            $this->translateAll($this->attribute->metaKeywords($this->uuid), ','),
            $entity['route'],
            $this->provider->currentYear()
        ];
        return str_replace($patterns, $replacements, $attribute);
    }

    public function routeGroups(): array
    {
        $routeGroups = [];
        $rawRouteGroups = $this->provider->routeGroups();
        foreach ($rawRouteGroups as $index => $routeGroup) {
//            echo "<pre>";
//            print_r($routeGroup);
//            echo "</pre>";
            $routeGroups[$index] = $this->setRouteGroup($routeGroup, $this->setRouteSubGroups($routeGroup));
        }
        return $routeGroups;
    }

    private function setRouteGroup($routeGroup, $routeSubGroups): array
    {
        return [
            'language' => $routeGroup['language'],
            'title' => $this->translateAll($routeGroup['title'], ' '),
            'routes' => $this->getTransformedRouteGroupRoutes($routeGroup),
            'route_sub_groups' => $routeSubGroups
        ];
    }

    private function setRouteSubGroups($routeGroup): array
    {
        $routeSubGroups = [];
        if ($routeGroup['route_sub_groups']) {
            $rawRouteSubGroups = $this->provider->routeSubGroups();
            foreach ($routeGroup['route_sub_groups'] as $routeSubGroup) {
                $subGroup = $rawRouteSubGroups[$routeSubGroup];
                $routeSubGroups[$routeSubGroup] = $this->setRouteGroup($subGroup, []);
            }
        }
        return $routeSubGroups;
    }

    private function getCatalogKey($descendantRoute, $key)
    {
        $attribute = $descendantRoute[$key];
        if ($key == 'title') {
            $attribute = $this->attribute->title($descendantRoute[$key]);
        }

        if ($key == 'innerHtml') {
            $attribute = $this->attribute->innerHtml($descendantRoute[$key]);
        }

        if ($attribute == $descendantRoute[$key]) {
            $catalog = $this->provider->catalog($this->uuid);
            $attribute = $catalog['emperor_coins'][$descendantRoute[$key]][$key];
        }
        return $attribute;
    }

    private function getTransformedRouteGroupRoutes($routeGroup): array
    {
        $descendantRoutes = $this->provider->descendantRoutes();
        $transformedRouteGroupRoutes = [];
        foreach ($routeGroup['routes'] as $routeGroupRoute) {
            $descendantRoute = $descendantRoutes[$routeGroupRoute];
            $descendantRoute['title'] = $this->translateAll(
                $this->getCatalogKey($descendantRoute, 'title'),
                ' '
            );
            $descendantRoute['innerHtml'] = $this->translateAll(
                $this->getCatalogKey($descendantRoute, 'innerHtml'),
                ' '
            );
            $transformedRouteGroupRoutes[$routeGroupRoute] = $descendantRoute;
        }
        return $transformedRouteGroupRoutes;
    }

    public function showMimeTypes(): bool
    {
        $entity = $this->provider->route();
        return in_array($entity['type'], $this->templateTypes);
    }

    private function translateAll($locales, $separator): string
    {
        return implode($separator, $this->locale->translateAll($locales));
    }
}