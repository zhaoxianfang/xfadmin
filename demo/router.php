<?php

/**
 * PHP 内置服务器路由器：把 /zxf/xfadmin/* 指向 resources/assets，
 * 其余请求交给 demo/index.php 处理。
 *
 * 运行：php -S 127.0.0.1:8900 demo/router.php
 */

$uri    = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$assets = __DIR__ . '/../resources/assets';
$prefix = '/zxf/xfadmin';

if (str_starts_with($uri, $prefix . '/')) {
    $rel  = ltrim(substr($uri, strlen($prefix) + 1), '/');
    $file = $assets . '/' . $rel;
    // 扩展名白名单（未知扩展一律 404，杜绝 .php 源码等被当作附件输出）
    $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
        'css'  => 'text/css; charset=utf-8',
        'js', 'mjs' => 'text/javascript; charset=utf-8',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'  => 'font/ttf',
        'json', 'map' => 'application/json',
        'pdf'  => 'application/pdf',
        default => null,
    };
    // realpath 包含性校验：文件必须真实位于资源根目录内（纵深防御，防符号链接/规范化差异越界）
    $realBase = realpath($assets);
    $realFile = realpath($file);
    if ($mime !== null
        && ! str_contains($rel, '..')
        && $realBase !== false && $realFile !== false
        && str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)
        && is_file($realFile)
    ) {
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=3600');
        readfile($realFile);
        exit;
    }
    http_response_code(404);
    exit;
}

// ---------------------------------------------------------------
// 演示 Mock API：/api/demo/* —— 让编辑 / 删除 / 状态切换 / 详情拉取
// 等交互在纯静态演示环境中也能形成完整闭环（不落库，返回标准 JSON）。
// ---------------------------------------------------------------
if (str_starts_with($uri, '/api/demo/')) {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    // 编辑/新建页面片段：GET /api/demo/staff/{id}/edit 与 GET /api/demo/staff/create
    // 返回带 [data-xf-page-content] 的 HTML，供前端 pageDialog 弹窗加载、表单自动接管提交
    $pm       = [];
    $isEdit   = $method === 'GET' && preg_match('#^/api/demo/staff/(\d+)/edit$#', $uri, $pm);
    $isCreate = $method === 'GET' && $uri === '/api/demo/staff/create';
    if ($isEdit || $isCreate) {
        header('Content-Type: text/html; charset=utf-8');
        $id     = $isEdit ? (int) $pm[1] : 0;
        $names  = ['', '陈晨', '林晚', '苏叶', '顾北', '沈渡'];
        $name   = $isEdit ? ($names[$id % 5 + 1] ?? '员工' . $id) : '';
        $email  = $isEdit ? 'user' . $id . '@demo.cn' : '';
        $action = $isEdit ? '/api/demo/staff/' . $id : '/api/demo/staff';
        echo '<div data-xf-page-content><form class="xf-edit-form" method="POST" action="' . htmlspecialchars($action) . '">'
            . ($isEdit ? '<input type="hidden" name="_method" value="PUT"><input type="hidden" name="id" value="' . $id . '">' : '')
            . '<div class="row g-3">'
            . '<div class="col-12 col-md-6"><label class="form-label">姓名 <span class="text-danger">*</span></label>'
            . '<input type="text" name="name" class="form-control form-control-sm" value="' . htmlspecialchars($name) . '" required></div>'
            . '<div class="col-12 col-md-6"><label class="form-label">邮箱</label>'
            . '<input type="email" name="email" class="form-control form-control-sm" value="' . htmlspecialchars($email) . '"></div>'
            . '<div class="col-12 col-md-6"><label class="form-label">部门</label>'
            . '<select name="dept" class="form-select form-select-sm"><option>技术部</option><option>产品部</option><option>市场部</option></select></div>'
            . '<div class="col-12 col-md-6"><label class="form-label">状态</label>'
            . '<div class="form-check form-switch mt-1"><input type="hidden" name="enabled" value="0">'
            . '<input type="checkbox" class="form-check-input" name="enabled" value="1"' . ($isEdit ? ' checked' : '') . '></div></div>'
            . '<div class="col-12"><label class="form-label">备注</label>'
            . '<textarea name="remark" class="form-control form-control-sm" rows="3" placeholder="演示环境不落库"></textarea></div>'
            . '</div><div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">'
            . '<button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>'
            . '<button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> ' . ($isEdit ? '保存' : '创建') . '</button>'
            . '</div></form></div>';
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');

    // 详情接口：GET /api/demo/staff/{id} —— 返回增量详情字段（viewRow ajax 演示）
    if ($method === 'GET' && preg_match('#^/api/demo/staff/(\d+)$#', $uri, $m)) {
        $id = (int) $m[1];
        echo json_encode([
            'code' => 200,
            'message' => 'ok',
            'data' => [
                'last_login'   => date('Y-m-d H:i:s', time() - $id * 3600),
                'login_count'  => 120 + $id * 7,
                'projects'     => [
                    ['name' => '订单中台重构', 'role' => '核心开发', 'status' => '进行中'],
                    ['name' => '数据大屏 V2',  'role' => '技术评审', 'status' => '已完成'],
                ],
                'security_log' => [
                    ['time' => date('m-d H:i', time() - 3600),  'title' => '登录成功', 'color' => 'success', 'text' => 'Chrome / macOS'],
                    ['time' => date('m-d H:i', time() - 86400), 'title' => '修改密码', 'color' => 'warning'],
                    ['time' => date('m-d H:i', time() - 172800), 'title' => '异地登录提醒', 'color' => 'danger', 'text' => 'IP 103.44.1.2'],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 写操作：POST / PUT / PATCH / DELETE 一律确认成功（演示环境不落库）
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        $tips = ['POST' => '操作成功', 'PUT' => '保存成功', 'PATCH' => '更新成功', 'DELETE' => '删除成功'];
        echo json_encode([
            'code'    => 200,
            'message' => $tips[$method] . '（演示环境不落库）',
            'data'    => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(404);
    echo json_encode(['code' => 404, 'message' => 'Not Found'], JSON_UNESCAPED_UNICODE);
    exit;
}

require __DIR__ . '/index.php';
