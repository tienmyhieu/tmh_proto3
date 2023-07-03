<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/defines.php');
require_once(__DIR__ . '/TmhAttribute.php');
require_once(__DIR__ . '/TmhComponent.php');
require_once(__DIR__ . '/TmhElement.php');
require_once(__DIR__ . '/TmhElementTree.php');
require_once(__DIR__ . '/TmhJson.php');
require_once(__DIR__ . '/TmhLocale.php');
require_once(__DIR__ . '/TmhProvider.php');
require_once(__DIR__ . '/TmhRender.php');

//echo "<pre>";
$json = new TmhJson();
$provider = new TmhProvider($json);
$attribute = new TmhAttribute($provider->attributes());
$locale = new TmhLocale($provider);
$component = new TmhComponent($attribute, $locale, $provider);

$element = new TmhElement();
$elementTree = new TmhElementTree($component, $element);
$render = new TmhRender($component, $elementTree);
$render->render();
//echo "</pre>";