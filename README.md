# riven/hyperf-generator

`php-amqplib` 的 Hyperf 友好型封装，为 Hyperf 生态系统提供代码自动生成支持。

## 安装

安装此包，请运行以下 Composer 命令：

```bash
composer require riven/hyperf-generator
```

## 支持的命令

### Controller (控制器)

```bash
# 生成控制器
php bin/hyperf.php generate --table=order --type=c

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=controller
```

### Validate (验证器)

```bash
# 生成验证器
php bin/hyperf.php generate --table=order --type=v

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=validate
```

### Service (服务)

```bash
# 生成服务
php bin/hyperf.php generate --table=order --type=s

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=service
```

### Data (数据)

```bash
# 生成数据类
php bin/hyperf.php generate --table=order --type=d

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=data
```

### Model (模型)

```bash
# 生成模型
php bin/hyperf.php generate --table=order --type=m

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=model
```

### Struct (结构体)

```bash
# 生成结构体
php bin/hyperf.php generate --table=order --type=st

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=struct
```

## 类型组合使用示例

```bash
# 生成控制器和模型
php bin/hyperf.php generate --table=order --type=c,m

# 生成服务、验证器和数据类
php bin/hyperf.php generate --table=order --type=s,v,d

# 生成所有类型文件
php bin/hyperf.php generate --table=order --type=c,v,s,d,m,st

# 指定表并生成特定类型
php bin/hyperf.php generate --table=order --table=users --type=c,v,s

# 生成特定类型到指定路径
php bin/hyperf.php generate --table=order --type=m,v --path=Admin

# 生成多个表的特定类型
php bin/hyperf.php generate --table=order,users --type=c,m
```

## 数据同步
### 同步操作
使用 setNumber() 方法或通过数组方式 $data['number'] 修改数据时，对象属性和数组键会保持同步。
```php
$data = $request->getStruct();
// 通过方法设置，属性和数组均同步
$data->setNumber(333);
dump($data->getAll());    // 'number' => 333
dump($data['number']);   // 333
dump($data->getNumber()); // 333

// 通过数组方式设置，属性和数组均同步
$data['number'] = 222;
dump($data->getAll());    // 'number' => 222
dump($data['number']);   // 222
dump($data->getNumber()); // 222
```

### 非同步操作
- 直接通过对象属性方式 $data->number 进行赋值，只会更改对象属性本身，而不会同步更新到内部的数组。
```php
$data = $request->getStruct();
// 直接赋值给对象属性，数组不会同步
$data->number = 444;
dump($data->getAll());    // 'number' 仍为 222
dump($data['number']);   // 仍为 222
dump($data->getNumber()); // 444 (仅对象属性被改变)
```
- 二维数组操作不同步，当处理二维数组或嵌套数据结构时，直接操作二维数组不会同步更新到对象属性：
```php
$data = $request->getStruct();
// 假设存在嵌套结构
$data['attach']['name'] = 'John';
// 此时对象属性不会自动更新，需要通过专门的方法或重新设置整个数组
dump($orderStruct['attach']); // array('name' => John)
dump($orderStruct->attach); // array() 仍为空数组


// 正确的做法1: 是使用提供的方法
$orderStruct->setAttach('name', 444);
var_export($orderStruct['attach']); // array('aaa' => 444)
var_export($orderStruct->attach); // array('aaa' => 444)

// 正确的做法2: 重新设置整个键值
$data['attach'] = ['name' => 'John']; // 重新设置整个键值
```
