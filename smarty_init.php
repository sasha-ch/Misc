<?php

//Smarty init example

$smarty = new Smarty;

$smarty->setTemplateDir(__DIR__ . '/templates/');
$smarty->setCompileDir(__DIR__ . '/compiled/');
$smarty->setConfigDir(__DIR__ . '/templates/');
$smarty->addPluginsDir(__DIR__ . '/../smarty/plugins');

$smarty->debugging = false;
$smarty->compile_check = true;
$smarty->force_compile = true;
$smarty->caching = false;
$smarty->error_reporting = ini_get('error_reporting');

//$smarty->loadFilter('filer1', 'filter2');

