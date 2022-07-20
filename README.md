<h1 align="center"> Response API </h1>

<p align="center"> A RESTful API package for the Laravel framework. </p>

### Installing

设置 `composer.json`

```json
{
    "config": {
        "secure-http": false
    }
}
```

#### 开发安装
```shell
$ composer config repositories.zfwy-api vcs https://git.zfwy100.com/common-package/zfwy-api-package.git
$ composer require zfwy/api:dev-main
```

如果要给这个包提交代码，为了方便开发, 如下配置

```shell
$ composer config repositories.zfwy-api path ../packages/zfwy-api
$ composer require zfwy/api:dev-main
```

#### 线上环境安装

```shell
$ composer config repositories.zfwy-api vcs https://git.zfwy100.com/common-package/zfwy-api-package.git
$ composer require zfwy/api:~1.0
```

### 发布配置文件

```shell
# 发布配置文件
$ php artisan vendor:publish --provider="Chuangke\Api\ApiServiceProvider" --tag="config"

# 发布语言文件
$ php artisan vendor:publish --provider="Chuangke\Api\ApiServiceProvider" --tag="lang"
```

### 修改 `App\Exceptions\Handler`

按照如下设置

```php
<?php

namespace App\Exceptions;

use Exception;
use Chuangke\Api\Exceptions\Helpers;

class Handler extends ExceptionHandler
{
    use Helpers;
    
    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        return $this->covertRenderToApiResponse($request, $exception);
    }
}
```

### Usage
#### 控制器中使用

```php
<?php

namespace App\Http\Controllers;

use Chuangke\Api\Routing\Helpers;

class UserController extends Controller
{
    use Helpers;
    
    public function index()
    {
        // 数组返回
        return $this->response->array([]);
        
        // 单条数据返回
        return $this->response->item(new UserResource($model));
        
        // 集合返回
        return $this->response->collection(UserResource::collection($models));
        
        // 分页
        return $this->response->collection(UserResource::collection(User::paginate(20)));
        
        // 添加 meta 信息
        return $this->response->collection(UserResource::collection(User::paginate(20)))
          ->meta('key', 'value');
          
        // 参数验证错误
        if ($var) {
            $this->response->errorUnprocessableEntity('密码错误');
        }
        
        // 内部错误
        if ($var) {
            $this->response->errorInternal();
        }
    }
} 
```

### 函数使用方式

```php
<?php

api_response()->array();
```

### License

MIT
