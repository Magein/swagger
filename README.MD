### 描述

 > 基于swagger的api在线接口文档
 
### 使用

 ```
    $swagger_data = new \SwaggerApi\SwaggerData();
    // 设置接口文档标题
    $swagger_data->setTitle('swagger api 在线接口文档');
    // 设置获取api json格式数据的路径
    $swagger_data->setJsonDataUrl('./index.php');
    
    $swagger = new \SwaggerApi\Api($swagger_data);
    
    // 渲染页面
    $swagger->display();
    
    // 获取数组数据
    // 通过数组数据可以修改返回的值，比如根据环境，定义host
    $data=$swagger->getData();
    // 修改host值
    if($_SERVER['HTTP_HOST']=='127.0.0.1'){
        $data['host']='127.0.0.1';
    }else{
        $data['host']='xx.xx.xx.xx';
    }
    
    // 剔除转义字符，此方法跟getJson输出的代码一致（为更改$data的情况下）
    echo $swagger->removeTransferred(json_encode($data));
    
    
    // 直接获取json数据（可解析的）
    $swagger->getJson();
 ``` 