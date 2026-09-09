# 常见问题（FAQ）

## 基础

**Q：必须发布静态资源吗？**

Laravel 不必 —— 会注册自托管路由（`/zxf/xfadmin/*`），由 `AssetController` 直接输出 vendor 内资源，
带 ETag 与一年强缓存。**生产环境建议发布**（Web 服务器直出更快）。
ThinkPHP **必须发布**（无自托管路由）。

**Q：需要 Node / Webpack / Vite 吗？**

不需要。所有第三方库已随包内置在 `resources/assets/plugins/`，纯静态引用。

**Q：支持哪些框架？**

核心层框架无关（原生 PHP 即可）。Laravel 11/12 与 ThinkPHP 8+ 有官方服务注册。
其它框架只需引入 autoload + `XfAdmin::config()`。

**Q：组件怎么输出？**

组件实现 `Stringable`，`echo` / `(string)` / `->render()` 均可：

```php
echo XfAdmin::card([...]);
$html = (string) XfAdmin::card([...]);
$html = XfAdmin::card([...])->render();
```

**Q：能在 Blade / 模板里用吗？**

可以：

```blade
{!! XfAdmin::card(['title' => 'x']) !!}
@xf('card', ['title' => 'x'])
```

## 组件与配置

**Q：传了参数没效果？**

先确认键名正确（组件忽略未知键）。可打印合并后的配置排查：

```php
print_r(XfAdmin::card(['title' => 'x'])->options());
```

**Q：数组参数出现莫名重复项？**

`array_replace_recursive` 对**索引数组**按「键位并集」合并，不是整体替换。
若 `defaults` 里该数组有默认值，调用方传部分数组会与默认值按位合并。
规避：设计时列表型默认留 `[]`；调用方传完整数组。

**Q：如何给组件加自定义 class / 属性？**

```php
XfAdmin::card([...])->addClass('shadow-sm', 'mb-3')->attr('data-role', 'panel')->id('my-card');
```

**Q：内容槽位（body/content）会被转义吗？**

**不会** —— 这些是 `raw()` 槽位，原样输出 HTML。
若要放用户输入，请自行转义：`'body' => Html::e($input)`。

**Q：为什么 `title` 被转义了但 `body` 没有？**

设计如此：`title`/`label`/`text` 是**纯文本**槽位（防 XSS）；
`body`/`content`/`footer` 是**内容**槽位（支持组件嵌套）。

**Q：图片路径怎么写？**

支持三种：

```php
'avatar' => 'users/user-1.jpg',                  // 包内 images/ 相对路径
'avatar' => 'https://cdn.example.com/a.jpg',     // 外链
'avatar' => 'data:image/png;base64,...',         // data URI
```

统一由 `$this->img()` 解析（空值返回透明 1×1 GIF，避免破图）。

## 页面与布局

**Q：`XfAdmin::page()` 还需要自己写 `<head>` 吗？**

不需要，`page()` 输出完整 HTML 文档（含 `head()` 与 `scripts()`）。

**Q：自定义布局时 head 和 scripts 的顺序？**

`scripts()` 必须在所有组件渲染后；`head()` 建议也在渲染后（或至少确认 CSS 已注册）。
推荐：先渲染内容为字符串，再输出 head。

**Q：怎么切换水平导航？**

三种方式之一：

```php
'layout' => 'horizontal'    // 或 'topnav'
'topnav' => [...]           // 传数组即启用（推荐）
```

**Q：菜单怎么高亮当前项？**

传 `current_url`（组件会去除首尾 `/` 后比对，父链自动展开）：

```php
'current_url' => request()->path(),
```

**Q：菜单支持权限过滤吗？**

组件不处理权限。请在生成菜单数组时自行过滤（见 [02-导航系统 §7](../04-进阶指南/02-navigation.md#7-权限过滤推荐做法)）。

## 表格

**Q：服务端模式和本地模式怎么选？**

数据 < 数千行用本地；更多用服务端（`server_side => true` + `XfAdmin::dataResponse()`）。

**Q：后端要自己解析 DataTables 参数吗？**

不用。`XfAdmin::dataResponse($rows, $params, $options)` 完成搜索/过滤/排序/分页，
返回 `{draw, recordsTotal, recordsFiltered, data}`。

**Q：过滤栏不生效？**

前端控件名必须与后端 `filters` 的键对应。例如 `daterange` 产出 `date_from`/`date_to`，
后端需配置同名规则。对照表见 [data-protocol §11](../02-核心架构/05-data-protocol.md#11-完整示例)。

**Q：表格里的操作按钮点了两次？**

已内置守卫（`initRecordDetailOps` 遇到表格内按钮会跳过）。
若自定义按钮同时写了 `data-xf-op` 与 `data-xf-act` 可能重复，去掉一个。

**Q：如何自定义单元格渲染？**

```php
// 方式一：内置渲染器
['key' => 'amount', 'render' => ['type' => 'money', 'prefix' => '￥']]

// 方式二：js: 全局函数
['key' => 'amount', 'render' => 'js:App.render.money']

// 方式三：注册渲染器
XFAdmin.registerCellRenderer('myRenderer', fn);
```

**Q：导出没有 PDF 选项？**

`export` 含 `pdf` 时会自动加载 `datatables-pdf`。
若写在 `options.buttons` 里（不在 `buttons`/`export` 键）则检测不到。

## 表单

**Q：为什么没有 `_token` 隐藏域？**

`Form` 的 `csrf` 默认是 `[]`（不注入）。需要时显式传 `'csrf' => true`。

**Q：AJAX 提交后不跳转？**

后端需返回**顶层** `url`：

```php
return response()->json(['ok' => true, 'message' => '成功', 'url' => '/admin/users']);
```

**Q：字段错误怎么显示？**

后端返回 422 + `errors`（Laravel 自动），前端按 `[name]` 回填：

```php
return response()->json(['message' => '校验失败', 'errors' => ['email' => ['已被占用']]], 422);
```

**Q：REST 方法（PUT/DELETE）怎么写？**

`<form>` 恒输出 `method="POST"`，用 `_method` 隐藏域：

```php
'<input type="hidden" name="_method" value="PUT">'
```

## 前端

**Q：`XFAdmin` 未定义？**

未输出 `XfAdmin::scripts()`（或自定义布局遗漏）。

**Q：组件不初始化？**

检查 `data-xf` 名称是否已在 `XFAdmin.widgets` 注册，
以及 `data-xf-config` 是否为合法 JSON。可手动重扫：`XFAdmin.scan(document.body)`。

**Q：AJAX 替换内容后交互失效？**

```js
XFAdmin.destroyWithin(oldNode);   // 清理
container.innerHTML = html;
XFAdmin.scan(container);          // 重新初始化
```

**Q：怎么监听表格行操作？**

```js
document.addEventListener('xf:action', e => console.log(e.detail.action, e.detail.row));
```

## 安全

**Q：如何防止 XSS？**

- 用户文本放进 `title`/`label`/`text` 等文本槽位（自动转义）；
- 放进 `body`/`content` 前自行 `Html::e()`；
- 内联 `<script>` 中的变量用 `Html::scriptJson()`；
- 运行 `php tools/selftest/xss_audit.php` 做模糊审计。

**Q：链接会被 `javascript:` 注入吗？**

不会 —— 所有链接类选项经 `safeUrl()` 协议白名单校验。

## 维护

**Q：改了组件源码后要更新文档吗？**

要：

```bash
php tools/gen_component_docs.php     # 重新生成组件文档
```

**Q：怎么验证包本身健康？**

```bash
bash tests/run-all.sh                # 语法 + 审计 + 冒烟 + 回归
bash tools/selftest/run.sh           # 浏览器级自测（需 Playwright）
```

**Q：升级后样式没变？**

改 `config/xfadmin.php` 的 `version`（追加 `?v=`），并清浏览器缓存。
若用发布方式，需重新 `vendor:publish --force`。
