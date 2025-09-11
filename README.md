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