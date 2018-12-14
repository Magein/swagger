<?php

namespace SwaggerApi;

class Api
{

    /**
     * @var SwaggerData $swaggerData
     */
    private $swaggerData;

    private $assign = [];

    public function __construct(SwaggerData $swaggerData)
    {
        $this->swaggerData = $swaggerData;
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
        if ($json_data_url) {
            $this->swaggerData->setJsonDataUrl($json_data_url);
        }
        $this->assign('title', $this->swaggerData->getTitle());
        $this->assign('json_data_url', $json_data_url);

        return $this->replaceAssign($html);
    }

    public function getJson($doc_file_path, $module_name)
    {
        if (empty($doc_file_path) || empty($module_name)) {
            echo '参数错误';
            exit();
        }

        $doc_path = $this->getPath($doc_file_path, $module_name);

        if (!is_dir($doc_path)) {
            echo '接口文档路径错误';
            exit();
        }

        $definitions = 'definitions';

        $paths = 'paths';

        $base_json = $this->getPath($doc_path, 'base.json');
        $swagger_data = $this->getFileContents($base_json);

        // 公共的字段信息
        $common_definitions = $this->getPath($doc_file_path, 'common', $definitions);
        // 项目定义的字段信息
        $doc_definitions = $this->getPath($doc_path, $definitions);

        $result = [];
        $this->getFile($common_definitions, $result);
        $this->getFile($doc_definitions, $result);

        if ($result) {
            foreach ($result as $item) {
                $key = pathinfo($item, PATHINFO_FILENAME);
                $swagger_data['definitions'][$key] = $this->getFileContents($item)[$key];
            }
        }

        $common_paths = $this->getPath($doc_file_path, 'common', $paths);
        $doc_paths = $this->getPath($doc_path, $paths);
        $result = [];
        $this->getFile($common_paths, $result);
        $this->getFile($doc_paths, $result);

        // 加载接口信息
        foreach ($result as $item) {

            $path_info = pathinfo($item);

            $dir_name = pathinfo($path_info['dirname'], PATHINFO_FILENAME);

            $item = $this->getFileContents($item);

            $key = array_keys($item);
            $key = array_shift($key);

            if ($dir_name == 'paths') {
                $dir_name = substr($key, 0, strpos($key, '/'));
            }

            // 处理标签
            if (!isset($item[$key]['post']['tags'])) {
                $item[$key]['post']['tags'][] = $dir_name;
            }

            $swagger_data[$paths][$key] = $item[$key];
        }

        $swagger_json = json_encode($swagger_data, JSON_UNESCAPED_UNICODE);

        return str_replace('\\/', '/', $swagger_json);
    }

    /**
     * @param mixed ...$_
     * @return array|string
     */
    private function getPath(...$_)
    {
        if ($_) {
            return implode('/', $_);
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



