<?php

namespace SwaggerApi;

class SwaggerData
{
    private $title = 'api接口文档';

    private $json_data_url = '';

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getJsonDataUrl()
    {
        return $this->json_data_url;
    }

    /**
     * @param string $json_data_url
     */
    public function setJsonDataUrl($json_data_url)
    {
        $this->json_data_url = $json_data_url;
    }
}