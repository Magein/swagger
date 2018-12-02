<?php


class Swagger
{

    public $api_doc_path = './document';

    public function index()
    {
        $doc_name = isset($_GET['doc_name']) ? $_GET['doc_name'] : '';

        if (empty($doc_name)) {
            echo '缺少项目名称';
            exit();
        }

        $index = file_get_contents('./index.html');

        echo str_replace('{$doc_name}', $doc_name, $index);;
        die();
    }

    public function doJson()
    {
        $doc_name = isset($_GET['doc_name']) ? $_GET['doc_name'] : '';

        if (empty($doc_name)) {
            exit();
        }

        $doc_path = $this->getPath($this->api_doc_path, $doc_name);

        $definitions = 'definitions';

        $paths = 'paths';

        $base_json = $this->getPath($doc_path, 'base.json');
        $swagger_data = $this->getFileContents($base_json);

        // 公共的字段信息
        $common_definitions = $this->getPath($this->api_doc_path, 'common', $definitions);

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

        $doc_paths = $this->getPath($doc_path, $paths);
        $result = [];
        $this->getFile($doc_paths, $result);

        // 加载接口信息
        foreach ($result as $item) {

            $path_info = pathinfo($item);

            $dir_name = pathinfo($path_info['dirname'], PATHINFO_FILENAME);

            $key = $dir_name . '/' . $path_info['filename'];

            $item = $this->getFileContents($item);

            if (!isset($item[$key]['post']['tags'])) {
                $item[$key]['post']['tags'][] = $dir_name;
            }

            $swagger_data[$paths][$key] = $item[$key];
        }

        $swagger_json = json_encode($swagger_data, JSON_UNESCAPED_UNICODE);

//        echo str_replace('\\', '', $swagger_json);
        echo $swagger_json;
        die();
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

$do_json = isset($_GET['do_json']) ? $_GET['do_json'] : '';
$api_document = new Swagger();
if ($do_json) {
    $api_document->doJson();
} else {
    $api_document->index();
}



