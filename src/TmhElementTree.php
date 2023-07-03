<?php

class TmhElementTree
{
    private $component;
    private $element;

    public function __construct(TmhComponent $component, TmhElement $element)
    {
        $this->component = $component;
        $this->element = $element;
    }

    public function getElementTree(): array
    {
        return [$this->element->docType(), $this->html()];
    }

    private function body(): array
    {
        return $this->element->body([$this->content()]);
    }

    private function content(): array
    {
        $contentDescription = $this->contentDescription();
        $routeGroups = $this->routeGroups();
        $elements = [$this->topMenu(), $this->navigation(), $this->contentTitle()];
        if ($contentDescription) {
            $elements[] = $contentDescription;
        }
        if ($routeGroups) {
            $elements[] = $routeGroups;
        }
        return $this->element->tmhContent($elements);
    }

    private function contentDescription(): array
    {
        $innerHtml = $this->component->contentDescription();
        if ($innerHtml) {
            return $this->element->span('tmh_content_description', [], $innerHtml);
        }
        return [];
    }

    private function contentTitle(): array
    {
        return $this->element->div('tmh_content_title', [], $this->component->contentTitle());
    }

    private function head(): array
    {
        $charset = $this->element->meta(['charset' => 'UTF-8']);
        $title = $this->element->title('meta.title');
        $description = $this->element->meta(['name' => 'description', 'content' => 'meta.description']);
        $keywords = $this->element->meta(['name' => 'keywords', 'content' => 'meta.keywords']);
        $viewport = $this->element->meta(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0']);
        $stylesheet = $this->element->link(['rel' => 'stylesheet', 'href' => '__CDN__css/tienmyhieu-domain.language.css']);
        $favIcon = $this->element->link(['rel' => 'icon', 'href' => '__IMAGES__favicon.png', 'type' => 'image/png']);

        return $this->element->head([
            $charset, $title, $description, $keywords, $viewport, $stylesheet, $favIcon
        ]);
    }

    private function html(): array
    {
        return $this->element->html(['lang' => $this->component->replace('domain.language')], [$this->head(), $this->body()]);
    }

    private function mimeTypes(): array
    {
        $pdf = $this->element->a(['class' => 'tmh_a', 'href' => 'pdf/route.route', 'title' => 'pdf'], 'pdf');
        $api = $this->element->a(['class' => 'tmh_a', 'href' => 'api/route.route', 'title' => 'api'], 'api');
        return $this->element->span('tmh_alternatives', [$api, $this->element->span('', [], ' - '), $pdf], '');
    }

    private function navigation(): array
    {
        $i = 0;
        $elements = [];
        $navigations = $this->component->navigation();
        foreach ($navigations as $navigation) {
            $isLast = $i == count($navigations) - 1;
            if ($isLast) {
                $elements[] = $this->element->span('', [], $navigation['innerHtml']);
            } else {
                $isHome = substr($navigation['href'], 0, 4) == 'http';
                $slash = $isHome ? '' : '/';
                $elements[] = $this->element->tmhA(
                    ['href' => $slash . $navigation['href'], 'title' => $navigation['title']],
                    $navigation['innerHtml']
                );
                $elements[] = $this->element->span('tmh_navigation_chevron', [], '&raquo;');
            }
            $i++;
        }
        return $this->element->div('tmh_navigation', $elements, '');
    }

    private function routeGroups(): array
    {
        $rawRouteGroups = $this->component->routeGroups();
        $routeGroups = $this->routeGroupsElements($rawRouteGroups);
        if ($routeGroups) {
            return $this->element->div('tmh_route_groups', $routeGroups, '');
        }
        return [];
    }

    private function routeGroupsElements($rawRouteGroups): array
    {
        $routeGroups = [];
        foreach ($rawRouteGroups as $routeGroup) {
            $elements = [];
            $elements[] = $this->element->span('tmh_route_group_title', [], $routeGroup['title']);
            $elements[] = $this->element->br();
            foreach ($this->routeGroupRoutes($routeGroup) as $route) {
                $elements[] = $route;
            }
            $routeGroups[] = $this->element->div('tmh_route_group', $elements, '');
        }
        return $routeGroups;
    }

    private function routeGroupRoutes($routeGroup): array
    {
        $elements = [];
        if (0 < count($routeGroup['routes'])) {
            foreach ($routeGroup['routes'] as $route) {
                if ($route['active']) {
                    $language = $routeGroup['language'];
                    $elements[] = $this->element->div('tmh_element_route', [$this->route($route, $language)], '');
                }
            }
        } else {
            foreach ($routeGroup['route_sub_groups'] as $routeSubGroup) {
                $elements[] = $this->routeSubGroupsRoutes($routeSubGroup);
            }
        }
        if (0 < count($elements)) {
            $elements[] = $this->element->br();
        }
        return $elements;
    }

    private function routeSubGroupsRoutes($routeSubGroup): array
    {
        $subElements = [];
        $subElements[] = $this->element->span('tmh_route_group_title', [], $routeSubGroup['title']);
        $subElements[] = $this->element->br();
        $language = $routeSubGroup['language'];
        foreach ($routeSubGroup['routes'] as $route) {
            if ($route['active']) {
                $subElements[] = $this->element->div('tmh_element_sub_route', [$this->route($route, $language)], '');
            }
        }
        return $this->element->div('tmh_element_sub_route_wrapper', $subElements, '');
    }

    private function route($route, $language): array
    {
        $attributes = ['class' => 'tmh_a', 'href' => $route['href'], 'title' => $route['title'], 'lang' => $language];
        return $this->element->a($attributes, $route['innerHtml']);
    }

    private function topMenu(): array
    {
        $elements = [];
        $separator = $this->element->span('', [], ' - ');
        foreach ($this->component->languages() as $otherDomain) {
            $attributes = ['class' => 'tmh_a', 'href' => $otherDomain['href'], 'hreflang' => $otherDomain['hreflang'], 'title' => $otherDomain['title']];
            $elements[] = $this->element->a($attributes, $otherDomain['innerHtml']);
            $elements[] = $separator;
        }
        unset($elements[count($elements) - 1]);
        if ($this->component->showMimeTypes()) {
            $elements[] = $this->mimeTypes();
        }
        return $this->element->div('tmh_languages', $elements, '');
//        return $this->element->div('tmh_top_menu', [$languages], '');
    }
}