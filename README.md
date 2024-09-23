# oneend/php-captcha

[![Latest Stable Version](https://poser.pugx.org/oneend/php-captcha/v/stable)](https://packagist.org/packages/oneend/php-captcha)
[![Total Downloads](https://poser.pugx.org/oneend/php-captcha/downloads)](https://packagist.org/packages/oneend/php-captcha)
[![License](https://poser.pugx.org/oneend/php-captcha/license)](https://packagist.org/packages/oneend/php-captcha)

`oneend/php-captcha` 是一个简单的 PHP 验证码生成工具，支持字符串和数学运算(+\-)验证码的生成，可以直接保存为图片文件，或以 Base64 格式输出。

## 功能特性

- 生成字符串和数学运算的验证码
- 支持使用 Session 或自定义的存取函数来保存和验证验证码
- 验证码可保存为 PNG 图片，或以 Base64 格式输出
- 支持验证码验证功能
- 通过 PHP 的 `GD` 库生成图像

## 安装

通过 [Composer](https://getcomposer.org/) 安装此包：

```bash
composer require oneend/php-captcha
```

## 使用示例

### 生成验证码并保存为图片

```php
<?php

require 'vendor/autoload.php';

use oneend\captcha\Captcha;

// 创建验证码对象
$captcha = Captcha::create();

// 使用 session 存储验证码，并生成图像
$captcha->useSession()->build()->save('captcha.png');

echo "Captcha saved as 'captcha.png'";
```

### 生成 Base64 编码的验证码图像

```php
<?php

require 'vendor/autoload.php';

use oneend\captcha\Captcha;

// 创建验证码对象
$captcha = Captcha::create();

// 生成 Base64 编码的图像数据
$base64 = $captcha->useSession()->build()->getBase64();

echo "<img src='data:image/png;base64,{$base64}' />";
```

### 数学运算验证码

```php
<?php

require 'vendor/autoload.php';

use oneend\captcha\Captcha;

// 创建数学运算验证码
$captcha = Captcha::create();

// 设置为数学运算并生成验证码
$captcha->useSession()->useMaths()->setContent([1, '+', 2])->build()->getBase64();

// 获取数学运算结果
$result = $captcha->getResult();
echo "Math result: $result";

// 验证用户输入
$isValid = $captcha->verify(3); // 预期结果为 3
echo $isValid ? "Verification passed" : "Verification failed";
```

### 自定义存储和验证

```php
<?php

require 'vendor/autoload.php';

use oneend\captcha\Captcha;

// 创建自定义存储验证码对象
$captcha = Captcha::create([
    'setFunc' => function ($key, $value) {
        return file_put_contents(__DIR__ . "/{$key}", $value);
    },
    'getFunc' => function ($key) {
        return file_get_contents(__DIR__ . "/{$key}");
    }
]);

// 生成验证码并保存
$captcha->setContent('abcd')->build()->getBase64();

// 验证输入
$key = $captcha->key;
$isValid = Captcha::create([
    'getFunc' => function ($key) {
        return file_get_contents(__DIR__ . "/{$key}");
    }
])->setKey($key)->verify('abcd');

echo $isValid ? "Verification passed" : "Verification failed";
```

## 许可证

该项目采用 MIT 许可证。详情请参阅 [LICENSE](LICENSE) 文件。

---