<?php

/**
 * 版权所有 ©Laravel2026, Inc. 保留所有权利。
 * https://github.com/laravel2026
 * E-mail: laravel2026@163.com
 */

namespace Modules\Cck\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cck\Lib\CckNodeFormatter;
use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('api/cck/node')]
class ApiCckNodeController extends Controller
{
    /**
     * 获取内容列表（分页）
     *
     * @bodyParam type string required 内容类型标识（ID 或机器名 name）
     * @bodyParam page int 页码，默认 1
     * @bodyParam per_page int 每页条数，默认 20，最大 100
     * @bodyParam is_published bool 筛选发布状态，不传返回全部
     * @bodyParam keyword string 按标题搜索
     * @bodyParam sort_field string 排序字段，默认 sort
     * @bodyParam sort_order string 排序方向，默认 desc
     */
    #[Get('list')]
    #[Post('list')]
    public function list(Request $request)
    {
        $type = $this->resolveType($request);
        if (! $type) {
            return response()->json(['code' => 1, 'msg' => '内容类型不存在']);
        }

        // 类型权限校验
        $authResult = $this->checkTypeAuth($type, $request);
        if ($authResult !== null) {
            return $authResult;
        }

        $query = CckNode::where('cck_type_id', $type->id);

        // 仅操作自己的内容
        if ($type->api_own_only) {
            $query->where('user_id', $request->user()->id);
        }

        // 未登录时只能看到已发布的内容
        if ($request->has('is_published') && $request->user()) {
            $query->where('is_published', $request->boolean('is_published'));
        } else {
            $query->where('is_published', true);
        }

        // 标题搜索
        if ($keyword = $request->input('keyword')) {
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        // 排序
        $sortField = in_array($request->input('sort_field', 'sort'), ['sort', 'id', 'published_at', 'created_at'])
            ? $request->input('sort_field', 'sort') : 'sort';
        $sortOrder = $request->input('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortOrder);

        $perPage = min((int) $request->input('per_page', 20), 100);
        $page = (int) $request->input('page', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'code' => 0,
            'data' => [
                'list' => CckNodeFormatter::formatMany($paginator->items()),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
            ],
            'msg' => 'ok',
        ]);
    }

    /**
     * 获取内容详情
     *
     * @bodyParam type string required 内容类型标识（ID 或机器名 name）
     * @bodyParam id int 内容 ID（与 slug 二选一）
     * @bodyParam slug string 内容标识（与 id 二选一）
     */
    #[Post('detail')]
    public function detail(Request $request)
    {
        $type = $this->resolveType($request);
        if (! $type) {
            return response()->json(['code' => 1, 'msg' => '内容类型不存在']);
        }

        // 类型权限校验
        $authResult = $this->checkTypeAuth($type, $request);
        if ($authResult !== null) {
            return $authResult;
        }

        $node = $this->findNode($request, $type->id);
        if (! $node) {
            return response()->json(['code' => 404, 'msg' => '内容不存在'], 404);
        }

        // 未发布的内容只有登录用户才能查看
        if (! $node->is_published && ! $request->user()) {
            return response()->json(['code' => 404, 'msg' => '内容不存在'], 404);
        }

        return response()->json([
            'code' => 0,
            'data' => CckNodeFormatter::format($node, $type),
            'msg' => 'ok',
        ]);
    }

    /**
     * 创建内容
     *
     * 需认证（auth:sanctum）。
     *
     * @bodyParam type string required 内容类型标识（ID 或机器名 name）
     * @bodyParam title string required 标题
     * @bodyParam slug string 唯一标识，不传自动生成
     * @bodyParam field_values object 动态字段值，键为字段机器名
     * @bodyParam is_published bool 是否发布，默认 false
     * @bodyParam published_at string 发布时间，不传则取当前时间
     * @bodyParam sort int 排序值，默认 0
     */
    #[Post('create', middleware: 'auth:sanctum')]
    public function create(Request $request)
    {
        $type = $this->resolveType($request);
        if (! $type) {
            return response()->json(['code' => 1, 'msg' => '内容类型不存在']);
        }

        // 类型权限校验
        $authResult = $this->checkTypeAuth($type, $request);
        if ($authResult !== null) {
            return $authResult;
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:cck_nodes,slug',
            'field_values' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'sort' => 'nullable|integer',
        ]);

        $data = [
            'cck_type_id' => $type->id,
            'title' => $request->input('title'),
            'slug' => $request->input('slug', ''),  // 可考虑自动生成
            'field_values' => $request->input('field_values', []),
            'is_published' => $request->boolean('is_published', false),
            'published_at' => $request->input('published_at', now()),
            'sort' => (int) $request->input('sort', 0),
            'user_id' => $request->user()?->id,
        ];

        if (empty($data['slug'])) {
            // 简单自动生成 slug
            $base = str_replace(' ', '-', trim($data['title']));
            $slug = $base;
            $i = 1;
            while (CckNode::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $node = CckNode::create($data);

        return response()->json([
            'code' => 0,
            'data' => CckNodeFormatter::format($node, $type),
            'msg' => '创建成功',
        ]);
    }

    /**
     * 更新内容
     *
     * 需认证（auth:sanctum）。
     *
     * @bodyParam id int required 内容 ID
     * @bodyParam title string 标题
     * @bodyParam slug string 唯一标识
     * @bodyParam field_values object 动态字段值，键为字段机器名
     * @bodyParam is_published bool 是否发布
     * @bodyParam published_at string 发布时间
     * @bodyParam sort int 排序值
     */
    #[Post('update', middleware: 'auth:sanctum')]
    public function update(Request $request)
    {
        $node = CckNode::findOrFail($request->input('id'));

        $type = CckType::find($node->cck_type_id);

        // 类型权限校验
        if ($type) {
            $authResult = $this->checkTypeAuth($type, $request);
            if ($authResult !== null) {
                return $authResult;
            }
            // 仅操作自己的内容
            if ($type->api_own_only && $node->user_id !== $request->user()->id) {
                return response()->json(['code' => 403, 'msg' => '无权操作'], 403);
            }
        }

        $request->validate([
            'id' => 'required|integer|exists:cck_nodes,id',
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:100|unique:cck_nodes,slug,' . $request->input('id'),
            'field_values' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'sort' => 'nullable|integer',
        ]);

        if ($request->has('title')) {
            $node->title = $request->input('title');
        }
        if ($request->has('slug')) {
            $node->slug = $request->input('slug');
        }
        if ($request->has('field_values')) {
            $node->field_values = array_merge($node->field_values ?? [], $request->input('field_values'));
        }
        if ($request->has('is_published')) {
            $node->is_published = $request->boolean('is_published');
        }
        if ($request->has('published_at')) {
            $node->published_at = $request->input('published_at');
        }
        if ($request->has('sort')) {
            $node->sort = (int) $request->input('sort');
        }

        $node->save();

        return response()->json([
            'code' => 0,
            'data' => CckNodeFormatter::format($node),
            'msg' => '更新成功',
        ]);
    }

    /**
     * 根据 type 参数解析内容类型
     */
    private function resolveType(Request $request): ?CckType
    {
        $type = $request->input('type');
        if (! $type) {
            return null;
        }

        // 优先按 ID 查询
        if (is_numeric($type)) {
            $result = CckType::find((int) $type);
            if ($result) {
                return $result;
            }
        }

        // 按机器名查询
        return CckType::where('name', $type)->first();
    }

    /**
     * 根据 id 或 slug 查找节点
     */
    private function findNode(Request $request, int $typeId): ?CckNode
    {
        if ($id = $request->input('id')) {
            return CckNode::where('cck_type_id', $typeId)
                ->where('id', (int) $id)
                ->first();
        }

        if ($slug = $request->input('slug')) {
            return CckNode::where('cck_type_id', $typeId)
                ->where('slug', $slug)
                ->first();
        }

        return null;
    }

    /**
     * 按类型权限配置校验接口访问权限
     *
     * @return \Illuminate\Http\JsonResponse|null 返回响应表示拒绝访问，null 表示通过
     */
    private function checkTypeAuth(CckType $type, Request $request): ?\Illuminate\Http\JsonResponse
    {
        // 接口需登录
        if ($type->api_auth_required && ! $request->user()) {
            return response()->json(['code' => 403, 'msg' => '请先登录'], 403);
        }

        // 仅操作自己的内容（需先登录）
        if ($type->api_own_only && ! $request->user()) {
            return response()->json(['code' => 403, 'msg' => '请先登录'], 403);
        }

        return null;
    }
}
