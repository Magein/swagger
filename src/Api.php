<?php

namespace SwaggerApi;

class Api
{

    /**
     * @var SwaggerData $swaggerData
     */
    private $swaggerData;

    private $assign = [];

    private $error = '';

    /**
     * Api constructor.
     * @param SwaggerData|null $swaggerData
     */
    public function __construct(SwaggerData $swaggerData = null)
    {
        if ($swaggerData == null) {
            $swaggerData = new SwaggerData();
        }
        $this->swaggerData = $swaggerData;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param $name
     * @param $value
     * @return array
     */
    private function assign($name, $value)
    {
        $this->assign[$name] = $value;

        return $this->assign;
    }

    /**
     * @param $html
     * @return null|string|string[]
     */
    private function replaceAssign($html)
    {
        if ($this->assign) {
            foreach ($this->assign as $name => $value) {
                $html = preg_replace('/\{\$' . $name . '\}/', $value, $html);
            }
        }

        return $html;
    }

    /**
     * @param $json_data_url
     * @return null|string|string[]
     */
    public function display($json_data_url)
    {
        $html = file_get_contents(__DIR__ . '/index.html');
        if (empty($json_data_url)) {
            $json_data_url = $this->swaggerData->getJsonDataUrl();
        }
        $this->assign('title', $this->swaggerData->getTitle());
        $this->assign('json_data_url', $json_data_url);

        return $this->replaceAssign($html);
    }

    /**
     * @param $doc_file_path
     * @return array|mixed|bool
     */
    private function readFile($doc_path)
    {
        // 定义的字段信息
        $definitions = 'definitions';
        // 存放文档的路径信息
        $paths = 'paths';

        // 获取配置信息数据
        $main_json = $this->getPath($doc_path, 'main.json');
        $swagger_data = $this->getFileContents($main_json);

        // 自定义的公共的字段信息
        $common_definitions = $this->getPath(pathinfo($doc_path, PATHINFO_DIRNAME), 'common', $definitions);

        // 模块中定义的字段信息
        $module_definitions = $this->getPath($doc_path, $definitions);

        /**
         * 读取自定义的字段信息，包含公共的和模块对应的
         *
         * 从文件中读取，并且添加到main数组中
         */
        $result = [];
        $this->getFile($common_definitions, $result);
        $this->getFile($module_definitions, $result);
        if ($result) {
            foreach ($result as $item) {
                $key = pathinfo($item, PATHINFO_FILENAME);
                $swagger_data['definitions'][$key] = $this->getFileContents($item)[$key];
            }
        }

        /**
         * 读取用于声明接口文档的文件信息，放到main数组中，用于最终的文档渲染
         */
        $common_paths = $this->getPath(pathinfo($doc_path, PATHINFO_DIRNAME), 'common', $paths);
        $module_paths = $this->getPath($doc_path, $paths);
        $result = [];
        $this->getFile($common_paths, $result);
        $this->getFile($module_paths, $result);


        /**
         * @param $url
         * @param $tag
         * @param $data
         */
        $concat = function ($url, $tag, $data) use (&$swagger_data, $paths) {
            $data['post'] = array_merge(['tags' => [$tag]], $data['post']);
            // 直接去掉前面的/ 在追加进去，防止文档中没有写
            $url = '/' . ltrim($url, '/');
            $swagger_data[$paths][$url] = $data;
        };
        foreach ($result as $file) {

            $file_info = pathinfo($file);

            $data = $this->getFileContents($file);

            if (isset($data['post'])) {
                $tag = pathinfo($file_info['dirname'], PATHINFO_BASENAME);
                $url = $tag . '/' . $file_info['filename'];
                $concat($url, $tag, $data);
            } else {
                foreach ($data as $key => $val) {
                    if ($key) {
                        $key = explode('/', $key);
                        $key = array_values(array_filter($key));
                        $tag = pathinfo($file_info['dirname'], PATHINFO_BASENAME);
                        if (count($key) == 1) {
                            $url = pathinfo($file_info['dirname'], PATHINFO_BASENAME) . '/' . $key[0];
                        } else {
                            $url = $key[0] . '/' . $key[1];
                        }
                        $concat($url, $tag, $val);
                    }
                }
            }
        }

        return $swagger_data;
    }

    /**
     * 获取数组数据
     * @param $doc_file_path
     * @return array|bool|mixed
     */
    public function getData($doc_file_path)
    {
        return $this->readFile($doc_file_path);
    }

    /**
     * 获取json数据
     * @param $doc_file_path
     * @param $module_name
     * @return mixed
     */
    public function getJson($doc_file_path, $module_name)
    {
        $data = $this->getData($doc_file_path, $module_name);

        return $this->removeTransferred(json_encode($data));
    }

    /**
     * 消除转义字符
     * @param $json
     * @return mixed
     */
    public function removeTransferred($json)
    {
        return str_replace('\\/', '/', $json);
    }

    /**
     * @param mixed ...$_
     * @return array|string
     */
    private function getPath(...$_)
    {
        if ($_) {
            return realpath(implode('/', $_));
        }
        return '';
    }

    /**
     * @param string $file_path
     * @return array|mixed
     */
    private function getFileContents($file_path)
    {
        if (is_file($file_path)) {
            return json_decode(file_get_contents($file_path), true);
        }
        return [];
    }

    private function getFile($file_name, &$result)
    {
        $handle = opendir($file_name);

        if (!is_resource($handle)) {
            return $result;
        }

        while ($item = readdir($handle)) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $path = $file_name . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->getFile($path, $result);
                continue;
            }
            if (is_file($path)) {
                $result[] = $path;
            }
        }

        return $result;
    }
}



