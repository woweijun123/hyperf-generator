# Hyperf Generator

Hyperf Generator 是一个基于 Hyperf 框架的代码生成工具，可以根据数据库表结构自动生成对应的 Model、Struct、Validator、Controller、Service 和 Data 类文件，大大提高开发效率。

## 功能特性

- 自动生成结构体（Struct）
- 自动生成模型（Model）
- 自动生成验证器（Validator）
- 自动生成控制器（Controller）
- 自动生成服务层（Service）
- 自动生成数据访问层（Data）

## 安装

```bash
composer require riven/hyperf-generator
```

## 配置

发布配置文件：

```bash
php bin/hyperf.php vendor:publish riven/hyperf-generator
```

配置文件位于 `config/autoload/generator.php`，可以配置数据库连接、字段类型映射等选项。

## 生成的文件说明

### 基础类说明

- `BaseController`：提供统一的响应格式
- `BaseData`：封装数据库操作方法
- `BaseModel`：扩展 Hyperf Model 功能
- `BaseService`：服务层基础类
- `BaseStruct`：结构体基础类
- `BaseValidator`：验证器基础类

### Struct（结构体）

- 继承自 `BaseStruct`
- 包含表的所有字段作为属性
- 提供字段的 getter、setter 和 has 方法

### Model（模型）

- 继承自 `BaseModel`
- 自动设置表名、主键和可填充字段
- 支持软删除、时间戳等特性

### Validator（验证器）

- 继承自 `BaseValidator`
- 包含基本的 CRUD 验证规则

### Controller（控制器）

- 继承自 `BaseController`
- 包含基本的 CRUD 操作方法
- 自动调用验证器和服务层

### Service（服务层）

- 继承自 `BaseService`
- 实现业务逻辑处理
- 包含创建、删除、更新、列表和详情方法

### Data（数据访问层）

- 继承自 `BaseData`
- 提供数据库操作方法
- 封装常用的增删改查操作

## 使用方法

在项目根目录下执行以下命令：

```bash
php bin/hyperf.php generate
```

### 命令行选项

- `-t, --table`：指定要生成的表名，多个表名用逗号分隔，默认为所有表
- `--type`：指定要生成的文件类型，多个类型用逗号分隔，支持以下类型：
    - `c` 或 `controller`：控制器
    - `v` 或 `validate`：验证器
    - `s` 或 `service`：服务层
    - `d` 或 `data`：数据访问层
    - `m` 或 `model`：模型
    - `st` 或 `struct`：结构体
- `-p, --path`：指定生成文件的路径
- `-f, --force`：覆盖已存在的文件

### 示例

#### 生成指定表的全部文件：

```bash
php bin/hyperf.php generate -t users,orders
```

#### 只生成模型和结构体：

```bash
php bin/hyperf.php generate -t users --type=model,struct
```

#### Controller (控制器)

```bash
# 生成控制器
php bin/hyperf.php generate --table=order --type=c

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=controller
```

#### Validate (验证器)

```bash
# 生成验证器
php bin/hyperf.php generate --table=order --type=v

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=validate
```

#### Service (服务)

```bash
# 生成服务
php bin/hyperf.php generate --table=order --type=s

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=service
```

#### Data (数据)

```bash
# 生成数据类
php bin/hyperf.php generate --table=order --type=d

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=data
```

#### Model (模型)

```bash
# 生成模型
php bin/hyperf.php generate --table=order --type=m

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=model
```

#### Struct (结构体)

```bash
# 生成结构体
php bin/hyperf.php generate --table=order --type=st

# 或使用完整名称
php bin/hyperf.php generate --table=order --type=struct
```

#### 类型组合使用示例

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

## 注意事项

1. 使用前请确保已正确配置数据库连接
2. 生成的文件会覆盖已存在的同名文件（使用 --force 选项时）
3. 建议在生成后检查并根据实际需求调整生成的代码