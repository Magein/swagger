<?php


require 'src/Api.php';
require 'src/SwaggerData.php';

$swagger = new \SwaggerApi\Api(new \SwaggerApi\SwaggerData());

$name = isset($_GET['f']) ? $_GET['f'] : '';

if (empty($name)) {
    echo $swagger->display('http://www.api.com/index.php?f=1&doc_name=index');
} else {
    $name = isset($_GET['doc_name']) ? $_GET['doc_name'] : '';
    echo $swagger->getJson('./document', $name);
    exit();
}

