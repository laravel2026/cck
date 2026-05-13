<?php

/**
 * 版权所有 ©Laravel2026, Inc. 保留所有权利。
 * https://github.com/laravel2026
 * E-mail: laravel2026@163.com
 */

namespace Modules\Cck\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Cck\Models\CckType;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('api/cck/type')]
class ApiCckTypeController extends Controller
{
    /**
     * 获取内容类型列表
     */
    #[Get('list')]
    #[Post('list')]
    public function list(Request $request)
    {
        $query = CckType::query();

        // 如果设置了仅操作自己，列表也只返回自己的类型
        $ownOnlyTypes = CckType::where('api_own_only', true)->pluck('id');
        if ($ownOnlyTypes->isNotEmpty()) {
            if (! $request->user()) {
                $query->whereNotIn('id', $ownOnlyTypes);
            } else {
                $query->where(function ($q) use ($ownOnlyTypes, $request) {
                    $q->whereNotIn('id', $ownOnlyTypes)
                      ->orWhere(function ($sub) use ($ownOnlyTypes, $request) {
                          $sub->whereIn('id', $ownOnlyTypes)
                              ->where('user_id', $request->user()->id);
                      });
                });
            }
        }

        // 需登录的类型，未登录用户不可见
        $authTypes = CckType::where('api_auth_required', true)->pluck('id');
        if ($authTypes->isNotEmpty() && ! $request->user()) {
            $query->whereNotIn('id', $authTypes);
        }

        $types = $query->with('fields')->orderBy('sort')->orderBy('id', 'desc')->get();

        return response()->json([
            'code' => 0,
            'data' => $types,
            'msg' => 'ok',
        ]);
    }

    /**
     * 获取内容类型详情
     */
    #[Get('detail')]
    #[Post('detail')]
    public function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required_without:name|integer|exists:cck_types,id',
            'name' => 'required_without:id|string|exists:cck_types,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1, 'msg' => '参数错误', 'errors' => $validator->errors()]);
        }

        $type = $request->has('id')
            ? CckType::find($request->input('id'))
            : CckType::where('name', $request->input('name'))->first();

        if (! $type) {
            return response()->json(['code' => 404, 'msg' => '内容类型不存在'], 404);
        }

        // 需登录的类型，未登录用户禁止查看
        if ($type->api_auth_required && ! $request->user()) {
            return response()->json(['code' => 403, 'msg' => '请先登录'], 403);
        }

        // 仅操作自己的类型，非本人不可查看
        if ($type->api_own_only && $type->user_id !== ($request->user()?->id ?: 0)) {
            return response()->json(['code' => 403, 'msg' => '无权查看'], 403);
        }

        $type->load('fields');

        return response()->json([
            'code' => 0,
            'data' => $type,
            'msg' => 'ok',
        ]);
    }

    /**
     * 创建内容类型
     */
    #[Post('create')]
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:cck_types,name',
            'display_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'show_in_menu' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'api_auth_required' => 'nullable|boolean',
            'api_own_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1, 'msg' => '参数错误', 'errors' => $validator->errors()]);
        }

        $data = $request->only([
            'name', 'display_name', 'description', 'icon', 'color',
            'show_in_menu', 'is_active', 'sort',
            'api_auth_required', 'api_own_only',
        ]);

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        $type = CckType::create($data);

        return response()->json([
            'code' => 0,
            'data' => $type,
            'msg' => '创建成功',
        ]);
    }

    /**
     * 更新内容类型
     */
    #[Post('update')]
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:cck_types,id',
            'name' => 'nullable|string|max:100|unique:cck_types,name,' . $request->input('id'),
            'display_name' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'show_in_menu' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort' => 'nullable|integer',
            'api_auth_required' => 'nullable|boolean',
            'api_own_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1, 'msg' => '参数错误', 'errors' => $validator->errors()]);
        }

        $type = CckType::findOrFail($request->input('id'));

        // api_auth_required 的接口需要登录
        if ($type->api_auth_required && ! $request->user()) {
            return response()->json(['code' => 403, 'msg' => '请先登录'], 403);
        }

        // 仅操作自己的类型，非本人不可修改
        if ($type->api_own_only && $type->user_id !== ($request->user()?->id ?: 0)) {
            return response()->json(['code' => 403, 'msg' => '无权操作'], 403);
        }

        $fields = [
            'name', 'display_name', 'description', 'icon', 'color',
            'show_in_menu', 'is_active', 'sort',
            'api_auth_required', 'api_own_only',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $type->$field = $request->input($field);
            }
        }

        $type->save();

        return response()->json([
            'code' => 0,
            'data' => $type,
            'msg' => '更新成功',
        ]);
    }

    /**
     * 删除内容类型
     */
    #[Post('delete')]
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:cck_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 1, 'msg' => '参数错误', 'errors' => $validator->errors()]);
        }

        $type = CckType::findOrFail($request->input('id'));

        // api_auth_required 的接口需要登录
        if ($type->api_auth_required && ! $request->user()) {
            return response()->json(['code' => 403, 'msg' => '请先登录'], 403);
        }

        // 仅操作自己的类型，非本人不可删除
        if ($type->api_own_only && $type->user_id !== ($request->user()?->id ?: 0)) {
            return response()->json(['code' => 403, 'msg' => '无权操作'], 403);
        }

        $type->delete();

        return response()->json([
            'code' => 0,
            'msg' => '删除成功',
        ]);
    }
}
