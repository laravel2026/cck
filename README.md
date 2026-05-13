# CCK — 内容构建工具包

免代码的动态内容管理模块，支持可视化定义内容类型和字段，自动生成管理界面和 API。

## CckNode 操作

所有内容数据通过 `CckNode` 类操作。`field_values` 存储动态字段值（JSON 对象），查询结果自动格式化输出，字段值已处理好（图片转 URL、下拉转 label、关联查标题等）。

```php
use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;
```

### 读取一条

```php
// 按 ID — 查询出来就是格式化好的
$node = CckNode::find(1);                            // ?CckNode

// 按 slug（全局唯一）
$node = CckNode::where('slug', 'my-product')->first();  // ?CckNode

// 按所属类型 slug + ID
$node = CckNode::byType('product')->where('id', 1)->first();  // ?CckNode

// 直接使用，toArray / toJson / API 响应自动格式化
// {
//     id: 1, title: "我的商品", slug: "my-product",
//     fields: {
//         price: { name: "price", display_name: "价格", field_type: "number", value: 99.0 },
//         images: { name: "images", display_name: "图片", field_type: "image", value: ["https://cdn.example.com/1.jpg"] }
//     },
//     type: { id: 1, name: "product", display_name: "商品" }
// }

// 原始字段值仍可直接访问
$raw = $node->field_values;     // ['price' => '99.00', 'images' => [...]]
$price = $node->field_values['price'] ?? null;   // 99.00
```

### 读取多条

```php
// 某个类型的所有已发布内容
$nodes = CckNode::byType('product')
    ->where('is_published', true)
    ->orderBy('sort', 'desc')
    ->get();                     // Collection|CckNode[]
```

### 分页

```php
$nodes = CckNode::byType('product')
    ->orderBy('sort', 'desc')
    ->paginate(20);              // LengthAwarePaginator，数据自动格式化
// $nodes->total(), $nodes->currentPage(), $nodes->lastPage()
```

### 多条件查询

```php
$nodes = CckNode::byType('product')
    ->where('is_published', true)
    ->where('title', 'like', '%关键词%')
    ->orderBy('sort', 'desc')
    ->paginate(20);
```

### 按动态字段查询

`field_values` 是 JSON 字段，支持 MySQL JSON 语法直接筛选：

```php
// 字段等于某个值
CckNode::byType('product')->where('field_values->price', '99.00')->get();

// JSON 数组包含
CckNode::byType('product')->whereJsonContains('field_values->tags', 'hot')->get();

// 嵌套对象
CckNode::byType('product')->where('field_values->address->city', '北京')->get();

// 字段模糊搜索
CckNode::byType('product')->where('field_values->description', 'like', '%关键词%')->get();

// 字段存在
CckNode::byType('product')->whereNotNull('field_values->price')->get();
```

### 添加

```php
$node = CckNode::create([
    'cck_type_id' => CckType::where('name', 'product')->value('id'),
    'title' => '我的商品',
    'slug' => 'my-product',              // 可选，留空可自行生成
    'field_values' => [
        'price' => '99.00',
        'images' => ['uploads/images/1.jpg'],
        'is_hot' => true,
    ],
    'is_published' => true,
    'published_at' => now(),
]);

$node->toArray();  // 自动格式化输出
```

### 更新

```php
$node = CckNode::find(1);
$node->title = '新标题';
$node->field_values = array_merge($node->field_values ?? [], [
    'price' => '88.00',
]);
$node->is_published = false;
$node->save();
```

### 删除

```php
CckNode::find(1)?->delete();
```

## 输出结构

```json
{
    "id": 1,
    "title": "我的商品",
    "slug": "my-product",
    "is_published": true,
    "sort": 0,
    "published_at": "2026-05-13 21:00:00",
    "created_at": "2026-05-13 21:00:00",
    "updated_at": "2026-05-13 21:00:00",
    "type": { "id": 1, "name": "product", "display_name": "商品" },
    "fields": {
        "price": { "name": "price", "display_name": "价格", "field_type": "number", "value": 99.0 },
        "images": { "name": "images", "display_name": "图片", "field_type": "image", "value": ["https://cdn.example.com/1.jpg"] },
        "is_hot": { "name": "is_hot", "display_name": "是否热门", "field_type": "toggle", "value": true }
    }
}
```

## API 接口

统一响应格式：`{ "code": 0, "msg": "ok", "data": { ... } }`，`data` 中内容已自动格式化。

### 权限说明

| 接口 | 认证要求 | 说明 |
|---|---|---|
| `list` | 公开 | 默认只返回已发布内容；登录后可传 `is_published` 筛选 |
| `detail` | 公开 | 只能查看已发布内容；未发布内容只有登录用户可看 |
| `create` | 需 `auth:sanctum` | 任意登录用户可创建 |
| `update` | 需 `auth:sanctum` | 任意登录用户可更新 |

### 列表 POST `/api/cck/node/list`

```json
{ "type": "product", "page": 1, "per_page": 20, "is_published": true, "keyword": "搜索词", "sort_field": "sort", "sort_order": "desc" }
```

### 详情 POST `/api/cck/node/detail`

```json
{ "type": "product", "id": 1 }
```
或按 slug：`{ "type": "product", "slug": "my-product" }`

### 创建 POST `/api/cck/node/create`

需 `Authorization: Bearer <token>`

```json
{ "type": "product", "title": "我的商品", "slug": "my-product", "field_values": { "price": "99.00" }, "is_published": true }
```

### 更新 POST `/api/cck/node/update`

需 `Authorization: Bearer <token>`

```json
{ "id": 1, "title": "新标题", "field_values": { "price": "88.00" } }
```

---

## 内容类型 API

权限按内容类型各自配置，在编辑类型时设置「API 权限」区域的两个开关：

| 开关 | 默认 | 说明 |
|---|---|---|
| `api_auth_required` | 开启 | 所有 CRUD 接口（列表/详情/创建/更新/删除）是否需要登录 |
| `api_own_only` | 关闭 | 是否仅操作自己创建的类型（列表过滤 + 增删改校验） |

### 列表 GET/POST `/api/cck/type/list`

无需参数，返回所有类型的列表（按类型自身权限过滤）。

### 详情 GET/POST `/api/cck/type/detail`

```json
// 按 ID
{ "id": 1 }
// 按机器名
{ "name": "product" }
```

### 创建 POST `/api/cck/type/create`

需 `Authorization: Bearer <token>`

```json
{ "name": "product", "display_name": "商品", "description": "商品内容", "is_active": true }
```

### 更新 POST `/api/cck/type/update`

需 `Authorization: Bearer <token>`

```json
{ "id": 1, "display_name": "商品管理", "sort": 10 }
```

### 删除 POST `/api/cck/type/delete`

需 `Authorization: Bearer <token>`

```json
{ "id": 1 }
```
