<?php


require 'src/Api.php';
require 'src/SwaggerData.php';

$swagger = new \SwaggerApi\SwaggerData();
$swagger->setTitle('swagger api 在线接口文档');
$swagger->setJsonDataUrl('./index.php');

$swagger = new \SwaggerApi\Api($swagger);

$name = isset($_GET['f']) ? $_GET['f'] : '';

if (empty($name)) {
    echo $swagger->display('http://www.api.com/index.php?f=1&m=index');
} else {
    $name = isset($_GET['m']) ? $_GET['m'] : '';
    echo $swagger->getJson('document/index');
    exit();
}

