# AGENTS.md — CCK 模块 AI 辅助开发指南

本文件为 AI 编码助手提供上下文,帮助理解 `Modules\Cck` 模块的架构、约定和开发规范。

## 项目概览

CCK (Content Construction Kit) — 免代码的动态内容管理模块,支持可视化定义内容类型和字段,自动生成管理界面和 API。

## 目录结构

```
src/
├── Filament/               # Filament 管理后台资源(13 个文件)
├── Http/                   # HTTP 控制器
├── Lib/                    # 工具类(13 个文件)
├── Models/
│   ├── CckNode.php         # 内容节点模型
│   └── CckType.php         # 内容类型模型
├── Policies/               # 授权策略
└── Providers/
    └── CckServiceProvider.php  # 服务提供者
```

## 核心架构

### CckNode 操作

所有内容数据通过 `CckNode` 类操作。`field_values` 存储动态字段值(JSON 对象),查询结果自动格式化输出。

```php
use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;

// 读取
$node = CckNode::find(1);
$node = CckNode::byType('product')->where('id', 1)->first();

// 创建
$node = CckNode::create([
    'cck_type_id' => CckType::where('name', 'product')->value('id'),
    'title' => '我的商品',
    'field_values' => ['price' => '99.00', 'images' => [...]],
    'is_published' => true,
]);

// 查询
$nodes = CckNode::byType('product')
    ->where('is_published', true)
    ->where('field_values->price', '99.00')  // JSON 字段查询
    ->paginate(20);
```

### 输出结构

```json
{
    "id": 1,
    "title": "我的商品",
    "slug": "my-product",
    "type": { "id": 1, "name": "product", "display_name": "商品" },
    "fields": {
        "price": { "name": "price", "display_name": "价格", "field_type": "number", "value": 99.0 },
        "images": { "name": "images", "display_name": "图片", "field_type": "image", "value": ["url"] }
    }
}
```

## API 接口

统一响应格式: `{ "code": 0, "msg": "ok", "data": { ... } }`

### 内容节点

| 接口 | 方法 | 路径 | 认证 |
|------|------|------|------|
| 列表 | POST | `/api/cck/node/list` | 公开(已发布) |
| 详情 | POST | `/api/cck/node/detail` | 公开(已发布) |
| 创建 | POST | `/api/cck/node/create` | `auth:sanctum` |
| 更新 | POST | `/api/cck/node/update` | `auth:sanctum` |

### 内容类型

| 接口 | 方法 | 路径 | 认证 |
|------|------|------|------|
| 列表 | GET/POST | `/api/cck/type/list` | 按类型配置 |
| 详情 | GET/POST | `/api/cck/type/detail` | 按类型配置 |
| 创建 | POST | `/api/cck/type/create` | `auth:sanctum` |
| 更新 | POST | `/api/cck/type/update` | `auth:sanctum` |
| 删除 | POST | `/api/cck/type/delete` | `auth:sanctum` |

## 内容类型 API 权限

| 开关 | 默认 | 说明 |
|------|------|------|
| `api_auth_required` | 开启 | 所有 CRUD 接口是否需要登录 |
| `api_own_only` | 关闭 | 是否仅操作自己创建的类型 |

## 编码规范

- PSR-4 命名空间: `Modules\Cck\` → `src/`
- PHP 8.2+ 语法特性
- 使用 JSON 字段存储动态数据
