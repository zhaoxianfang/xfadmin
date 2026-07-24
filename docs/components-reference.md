# 组件详细参考（自动生成 · 数据输入 / 输出）

> 本文档由代码反射生成，覆盖全部 **99** 个组件，列出每个组件的别名、用途、输入参数（数据）与输出行为。
> 调用统一形式：`XfAdmin::<alias>(array $options)`。所有组件均支持通用键 `id` / `class` / `attributes`。
> 资源前缀统一为 `zxf/xfadmin`，无需发布即可在 `demo/` 中直接加载。
> 返回 → [组件总览](components.md)

## 数据输入 / 输出通用约定

- **输入**：统一为关联数组 `$options`。组件先与 `defaults()` 合并，再经 `attrs()` / `data()` / `columns()` 等 setter 处理。
  数组既可用 `XfAdmin::xxx(['key' => 'val'])` 传入，也可用 `XfAdmin::xxx([])->key('val')` 链式设置（组件均提供同名 setter）。
- **数据字段**：含用户数据的字段一律经 `Html::e()`（`ENT_QUOTES`，UTF-8，不二次编码）转义，防止 XSS；内联 `<script>` 配置使用 `Html::scriptJson()`（输出大写 `\u003C` 防 `</script>` 注入）。
- **输出（HTML）**：组件 `render()` 返回一个 HTML 字符串片段；整页组件（如 `page`/`authPage`/`errorPage`）返回完整 `<!DOCTYPE html>` 文档。
- **输出（CSS/JS 资源）**：组件在渲染期通过 `assets()` 声明所需插件，`XfAdmin` 自动去重登记到 `head()`（CSS + `config.js`）与 `scripts()`（JS + 初始化脚本），无需手动引入。
- **前端交互**：需要 JS 行为的组件在根元素输出 `data-xf="<widget>"`，由 `xfadmin.js` 或组件内联 `XFAdmin.register('<widget>', ...)` 统一初始化；部分组件派发 `xf.<name>.*` 标准事件供监听。
- **通用键**：`id`（缺省自动生成唯一 ID，保证多次调用不冲突）、`class`（追加到根元素，支持字符串/数组）、`attributes`（额外 HTML 属性数组）。


## 布局与页面

### `authPage`

认证页骨架（登录 / 注册 / 找回密码 / 锁屏等）

- **输入示例（数据）**：
```php
echo XfAdmin::authPage([
    'title'    => '登录',
    'heading'  => '欢迎回来',
    'subheading' => '请输入账号密码继续',
    'content'  => XfAdmin::form([...]),   // 卡片内内容（任意组件/HTML）
    'below'    => '<p>还没有账号？<a href="/register">注册</a></p>',
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `comingSoon`

即将上线页（pages-coming-soon.html）—— 含倒计时

- **前端控件**：`data-xf="countdown"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
echo XfAdmin::comingSoon([
    'heading'  => '即将上线',
    'message'  => '我们正在努力，敬请期待！',
    'deadline' => '2026-12-31 00:00:00',
    'image'    => null,
```
- **输出**：带 `data-xf="countdown"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `customizer`

主题定制面板（offcanvas）——皮肤 / 明暗 / 顶栏色 / 菜单色 / 侧栏尺寸 / 布局位置 与模板 app.js 联动（元素 name 与 id 必须保持模板约定）

- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `emptyState`

空状态占位（pages-empty.html / pages-search-results.html 无结果）

- **输入示例（数据）**：
```php
XfAdmin::emptyState([
    'icon'   => 'ti ti-inbox',
    'image'  => null,               // 或用图片替代图标
    'title'  => '暂无数据',
    'text'   => '当前还没有任何记录',
    'action' => '<a href="#" class="btn btn-primary">新建</a>',
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `errorPage`

错误页（404 / 500 / 503 …）

- **输入示例（数据）**：
```php
echo XfAdmin::errorPage([
    'code'    => 404,
    'heading' => '页面不存在',
    'message' => '您访问的页面不存在或已被移动。',
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `footer`

页脚

- **输入示例（数据）**：
```php
XfAdmin::footer(['text' => '© 2026 XX公司', 'right' => '<a href="#">帮助</a>'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `lockScreen`

锁屏页（auth-lock-screen.html）—— 全屏锁定，输入密码解锁

- **前端控件**：`data-xf="pw-toggle"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::lockScreen([
    'user'    => ['name' => '张三', 'avatar' => 'users/avatar-1.jpg'],
    'action'  => '/unlock',
    'heading' => '已锁定',
])
```
- **输出**：带 `data-xf="pw-toggle"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `maintenance`

维护中页（maintenance.html）

- **输入示例（数据）**：
```php
echo XfAdmin::maintenance([
    'heading' => '网站维护中',
    'message' => '我们正在进行系统升级，稍后回来。',
    'image'   => null,
    'contact' => 'support@example.com',
]);
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `page`

整页骨架（完整 HTML 文档） 一行代码渲染完整后台页面，自动组装：主题属性 + 侧边栏 + 顶栏 + 水平导航 + 页面标题 + 内容 + 页脚 + 主题定制面板 + 全部按需资源（去重加载）。

- **输入示例（数据）**：
```php
echo XfAdmin::page([
    'title'   => '仪表盘',
    'layout'  => 'vertical',            // vertical | horizontal | dual
    'theme'   => ['mode' => 'light', 'menu_color' => 'dark', 'sidenav_size' => 'default', ...],
    'menu'    => [ ...Menu items... ],  // 侧栏与水平导航共用，或分别传 sidenav.menu / topnav.menu
    'sidenav' => [...] | false,
    'topbar'  => [...] | false,
    'page_title' => ['title' => '仪表盘', 'breadcrumb' => [...]],
    'content' => $components,           // 字符串 / 组件 / 数组（可混排任意组件）
    'footer'  => [...] | false,
    'customizer' => true,
]);
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `pageTitle`

页面标题 + 面包屑

- **输入示例（数据）**：
```php
XfAdmin::pageTitle([
    'title'      => '用户管理',
    'breadcrumb' => [['text' => '首页', 'url' => '/'], ['text' => '系统'], ['text' => '用户管理', 'active' => true]],
    'actions'    => '右侧自定义HTML（可选，替代面包屑）',
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `sidenav`

侧边栏（Logo + 可选用户卡片 + 无限极菜单）

- **输入示例（数据）**：
```php
XfAdmin::sidenav([
    'brand' => ['name' => 'XfAdmin', 'logo' => '/logo.png', 'logo_sm' => '/logo-sm.png', 'url' => '/'],
    'user'  => ['name' => '张三', 'role' => '管理员', 'avatar' => '/a.jpg', 'items' => [['text'=>'退出','url'=>'/logout','icon'=>'ti ti-logout-2']]],
    'menu'  => [ ...同 Menu items... ] | Menu 实例 | 原始 HTML,
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `topnav`

水平导航（horizontal 布局，配合 <html data-layout="topnav">）

- **输入示例（数据）**：
```php
XfAdmin::topnav(['menu' => [ ...同 Menu items... ]])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `topbar`

顶部导航栏

- **输入示例（数据）**：
```php
XfAdmin::topbar([
    'brand'         => true,            // 顶栏品牌 logo（horizontal 布局用）
    'search'        => true,            // 搜索框
    'left'          => '自定义HTML',     // 左侧附加插槽
    'theme_toggle'  => true,            // 明暗切换按钮
    'fullscreen'    => true,            // 全屏按钮
    'customizer'    => true,            // 主题定制按钮
    'languages'     => [['flag'=>..,'name'=>'中文','code'=>'zh'], ...],
    'notifications' => ['count' => 3, 'items' => [['title'=>..,'text'=>..,'time'=>..,'avatar'=>..,'icon'=>..,'url'=>..]], 'all_url' => '#'],
    'user'          => ['name'=>'张三','role'=>'管理员','avatar'=>'/a.jpg','items'=>[['text'=>'退出','icon'=>'ti ti-logout-2','url'=>'/logout']]],
    'right'         => '自定义HTML',     // 右侧附加插槽
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。


## 栅格

### `col`

栅格列

- **输入示例（数据）**：
```php
XfAdmin::col(['width' => 6, 'content' => ...])
XfAdmin::col(['width' => ['md' => 6, 'xl' => 4], 'offset' => ['md' => 3], 'content' => ...])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `row`

栅格行

- **输入示例（数据）**：
```php
XfAdmin::row([
    'gutter' => 3,                        // g-3；也可 ['x' => 2, 'y' => 3]
    'cols'   => [
        ['width' => 6, 'content' => $cardA],                 // col-6
        ['width' => ['md' => 6, 'xl' => 4], 'content' => $cardB],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。


## 表格

### `dataTable`

全功能数据表格（基于 DataTables，前端由 xfadmin.js 自动初始化） 支持：搜索、排序、分页、多选、导出（Excel/CSV/打印/PDF）、固定表头、 响应式折叠、列筛选、AJAX / 服务端模式、自定义列渲染模板等

- **前端控件**：`data-xf="datatable"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::dataTable([
    'columns' => [
        'id'     => ['label' => 'ID', 'sortable' => true],
        'name'   => '姓名',
        'status' => ['label' => '状态', 'badges' => ['启用' => 'success', '禁用' => 'danger']],
        'op'     => ['label' => '操作', 'template' => '<a href="/user/{id}/edit" class="btn btn-sm btn-soft-primary">编辑</a>', 'sortable' => false],
```
- **输出**：带 `data-xf="datatable"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `table`

静态表格（多种渲染风格）

- **输入示例（数据）**：
```php
XfAdmin::table([
    'columns' => [
        'name' => '姓名',
        'age'  => ['label' => '年龄', 'class' => 'text-center'],
        'op'   => ['label' => '操作', 'format' => fn ($row) => '<a href="/edit/' . $row['id'] . '">编辑</a>', 'raw' => true],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。


## 表单

### `check`

复选框 / 单选框 / 开关（单个或一组）

- **输入示例（数据）**：
```php
XfAdmin::check(['type' => 'switch', 'name' => 'enabled', 'label' => '启用', 'checked' => true])
XfAdmin::check([
    'type'    => 'radio',
    'name'    => 'gender',
    'inline'  => true,
    'value'   => 'f',
    'options' => ['m' => '男', 'f' => '女'],
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `colorPicker`

颜色选择器（Pickr）

- **前端控件**：`data-xf="pickr"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::colorPicker(['name' => 'color', 'label' => '主题色', 'value' => '#3e60d5', 'theme' => 'classic'])
```
- **输出**：带 `data-xf="pickr"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `dateRange`（别名：`dateRange`, `dateRangePicker`）

日期 / 日期范围 / 日期时间选择器（Date Range Picker）

- **前端控件**：`data-xf="daterangepicker"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::dateRange(['name' => 'date', 'label' => '日期', 'single' => true])
XfAdmin::dateRange(['name' => 'range', 'label' => '时段', 'format' => 'YYYY-MM-DD', 'ranges' => true])
```
- **输出**：带 `data-xf="daterangepicker"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `editor`

富文本编辑器（Quill / Summernote）

- **前端控件**：`data-xf="summernote"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::editor(['name' => 'content', 'label' => '正文', 'driver' => 'quill', 'theme' => 'snow', 'height' => 300, 'value' => '<p>初始内容</p>'])
```
- **输出**：带 `data-xf="summernote"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `form`

表单容器（支持浏览器原生校验样式 / AJAX 提交 / 行内布局）

- **输入示例（数据）**：
```php
XfAdmin::form([
    'action'  => '/users',
    'method'  => 'POST',
    'validation' => true,      // Bootstrap 客户端校验（needs-validation）
    'ajax'    => true,         // xfadmin.js 接管提交，触发 xf.form.success/error 事件
    'fields'  => [ Input、Select、Check ... 组件或 HTML 的数组 ],
    'buttons' => '<button class="btn btn-primary" type="submit">提交</button>',
    'csrf'    => ['_token' => 'xxx'],   // 附加隐藏域
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `input`

输入框（text/email/password/number/... + 输入掩码 + 标签输入 + 前后缀组）

- **输入示例（数据）**：
```php
XfAdmin::input(['name' => 'email', 'type' => 'email', 'label' => '邮箱', 'required' => true])
XfAdmin::input(['name' => 'phone', 'label' => '电话', 'mask' => '999-9999-9999'])
XfAdmin::input(['name' => 'tags', 'label' => '标签', 'tags' => true, 'value' => 'php,laravel'])
XfAdmin::input(['name' => 'price', 'prepend' => '￥', 'append' => '.00'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `maskedInput`

输入掩码（Inputmask，form-pickers.html / form-other-plugins.html）

- **前端控件**：`data-xf="inputmask"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::maskedInput([
    'name'  => 'phone',
    'label' => '手机号',
    'mask'  => '999-9999-9999',   // 或 alias: 'email' / 'currency' / 'datetime'
    'value' => '',
    'placeholder' => '___-____-____',
])
```
- **输出**：带 `data-xf="inputmask"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `passwordStrength`

密码强度计（misc-pass-meter）

- **前端控件**：`data-xf="pw-strength"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::passwordStrength([
    'name'     => 'password',
    'label'    => '密码',
    'value'    => '',
```
- **输出**：带 `data-xf="pw-strength"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `select`

下拉选择（原生 / Choices.js 增强 / Select2 增强，支持分组、多选、搜索、远程）

- **输入示例（数据）**：
```php
XfAdmin::select([
    'name'    => 'city',
    'label'   => '城市',
    'options' => ['bj' => '北京', 'sh' => '上海'],                 // 或 [['value'=>,'label'=>,'disabled'=>]]
    'groups'  => ['直辖市' => ['bj' => '北京'], ...],              // 分组
    'value'   => 'bj',            // 多选传数组
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `slider`

范围滑块（noUiSlider）

- **前端控件**：`data-xf="slider"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::slider(['name' => 'price', 'label' => '价格区间', 'min' => 0, 'max' => 1000, 'value' => [100, 500], 'tooltips' => true])
```
- **输出**：带 `data-xf="slider"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `tags`

标签输入（Tagify，form-other-plugins.html）

- **前端控件**：`data-xf="tagify"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::tags([
    'name'      => 'tags',
    'label'     => '标签',
    'value'     => ['php', 'laravel'],
    'whitelist' => ['php','laravel','vue','react'],
    'max'       => 5,
    'placeholder' => '输入后回车',
])
```
- **输出**：带 `data-xf="tagify"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `textarea`

多行文本框

- **输入示例（数据）**：
```php
XfAdmin::textarea(['name' => 'remark', 'label' => '备注', 'rows' => 4])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `upload`

文件上传（原生 / Dropzone 拖拽 / FilePond）

- **前端控件**：`data-xf="dropzone"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::upload(['name' => 'file', 'label' => '附件'])                                  // 原生
XfAdmin::upload(['driver' => 'dropzone', 'url' => '/upload', 'label' => '拖拽上传'])
XfAdmin::upload(['driver' => 'filepond', 'name' => 'avatar', 'multiple' => true])
```
- **输出**：带 `data-xf="dropzone"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `wizard`

分步向导（form-wizard.html）—— 纯原生 JS 驱动

- **前端控件**：`data-xf="wizard"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::wizard([
    'steps' => [
        ['title' => '账户', 'icon' => 'ti ti-user', 'content' => '第一步内容 HTML'],
        ['title' => '资料', 'icon' => 'ti ti-file', 'content' => '第二步内容'],
        ['title' => '完成', 'icon' => 'ti ti-check', 'content' => '完成'],
```
- **输出**：带 `data-xf="wizard"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。


## 图表与地图

### `apexChart`

ApexCharts 图表（折线/面积/柱状/条形/饼图/环形/雷达/热力图/K线/迷你走势 sparkline 等全部类型）

- **前端控件**：`data-xf="apexchart"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::apexChart([
    'type'   => 'line',
    'height' => 350,
    'series' => [['name' => '销量', 'data' => [10, 41, 35, 51]]],
    'labels' => ['一月', '二月', '三月', '四月'],       // 便捷 xaxis.categories / 饼图 labels
    'colors' => ['#3e60d5'],
```
- **输出**：带 `data-xf="apexchart"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `echart`

Apache ECharts 图表（支持全部 ECharts 图表类型与配置）

- **前端控件**：`data-xf="echart"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::echart([
    'height'  => 350,
    'options' => [
        'xAxis'  => ['type' => 'category', 'data' => ['Mon', 'Tue']],
        'yAxis'  => ['type' => 'value'],
        'series' => [['type' => 'bar', 'data' => [120, 200]]],
```
- **输出**：带 `data-xf="echart"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `leafletMap`

Leaflet 交互地图（maps-leaflet.html） 说明：Leaflet 的 JS/CSS 已本地内置（离线可用），但地图底图瓦片(tiles)通常来自在线 瓦片服务（如 OpenStreetMap）。设置 `tiles=null` 可完全离线渲染（仅显示标记/图形，无底图）。

- **前端控件**：`data-xf="leaflet-map"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::leafletMap([
    'height'  => 400,
    'center'  => [39.9, 116.4],
    'zoom'    => 11,
    'tiles'   => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', // null=离线无底图
    'markers' => [['lat'=>39.9,'lng'=>116.4,'title'=>'总部','popup'=>'说明']],
    'circles' => [['lat'=>39.9,'lng'=>116.4,'radius'=>1200,'color'=>'#3e60d5']],
```
- **输出**：带 `data-xf="leaflet-map"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `vectorMap`

矢量地图（jsVectorMap）

- **前端控件**：`data-xf="vectormap"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::vectorMap([
    'map'     => 'world',      // world | world_merc
    'height'  => 360,
    'markers' => [['name' => 'Beijing', 'coords' => [39.9, 116.4]]],
    'options' => [ ...透传 jsVectorMap 配置... ],
])
```
- **输出**：带 `data-xf="vectormap"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。


## UI 组件

### `accordion`

手风琴 / 折叠面板

- **输入示例（数据）**：
```php
XfAdmin::accordion([
    'items' => [
        ['title' => '第一项', 'content' => '...', 'open' => true],
        ['title' => '第二项', 'content' => '...'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `alert`

警告提示框

- **输入示例（数据）**：
```php
XfAdmin::alert(['variant' => 'success', 'text' => '操作成功', 'dismissible' => true, 'icon' => 'ti ti-check'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `avatar`

头像（图片 / 文字缩写 / 图标 / 分组堆叠）

- **输入示例（数据）**：
```php
XfAdmin::avatar(['src' => '/a.jpg', 'size' => 'md', 'rounded' => 'circle'])
XfAdmin::avatar(['text' => 'ZS', 'variant' => 'primary', 'size' => 'lg'])
XfAdmin::avatar(['group' => [['src' => '/a.jpg'], ['text' => '+5', 'variant' => 'info']]])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `badge`

徽章

- **输入示例（数据）**：
```php
XfAdmin::badge(['text' => '新', 'variant' => 'danger', 'pill' => true])
XfAdmin::badge(['text' => '5', 'variant' => 'primary', 'soft' => true, 'icon' => 'ti ti-bell'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `breadcrumb`

面包屑（独立使用；页面标题栏内置面包屑见 PageTitle）

- **输入示例（数据）**：
```php
XfAdmin::breadcrumb(['items' => [['text' => '首页', 'url' => '/'], ['text' => '列表']]])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `button`

按钮（支持 soft/outline/ghost 风格、图标、加载态 Ladda、链接按钮、模态/抽屉触发）

- **输入示例（数据）**：
```php
XfAdmin::button(['text' => '保存', 'variant' => 'primary', 'type' => 'submit'])
XfAdmin::button(['text' => '删除', 'variant' => 'danger', 'soft' => true, 'icon' => 'ti ti-trash'])
XfAdmin::button(['text' => '打开', 'toggle' => 'modal', 'target' => '#my-modal'])
XfAdmin::button(['text' => '提交', 'ladda' => true])   // 点击自动转圈
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `card`

卡片

- **输入示例（数据）**：
```php
XfAdmin::card([
    'title'    => '卡片标题',
    'subtitle' => '副标题',
    'tools'    => ['collapse', 'refresh', 'close'],   // 或自定义 HTML
    'actions'  => '头部右侧自定义HTML',
    'body'     => '内容（任意组件/HTML）',
    'footer'   => '页脚',
    'padding'  => true,    // false 时 body 无内边距（适合放表格）
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `carousel`

轮播

- **输入示例（数据）**：
```php
XfAdmin::carousel([
    'items' => [
        ['image' => '/1.jpg', 'caption' => '<h5>标题</h5><p>描述</p>'],
        ['content' => '<div>自定义任意HTML</div>'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `chip`

标签/胶囊（Chip / Tag）—— 可带头像、图标、关闭按钮

- **前端控件**：`data-xf="chip-close"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::chip([
    'label'    => '张三',
    'avatar'   => 'users/avatar-1.jpg',
    'icon'     => null,
    'variant'  => 'light',
    'dismissible' => true,
    'href'     => null,
])
```
- **输出**：带 `data-xf="chip-close"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `collapse`

折叠（Bootstrap Collapse）

- **输入示例（数据）**：
```php
XfAdmin::collapse([
    'trigger'   => '切换显示',      // 触发按钮内容
    'body'      => '被折叠的内容',
    'open'      => false,
    'trigger_class' => 'btn btn-primary',
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `descriptionList`

描述列表（键值对，详情页常用：invoice / product / order 详情）

- **输入示例（数据）**：
```php
XfAdmin::descriptionList([
    'items' => [
        '订单号' => '#12345',
        '状态'   => XfAdmin::badge(['text' => '已支付', 'variant' => 'success']),
        ['label' => '备注', 'value' => '尽快发货', 'raw' => true],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `dropdown`

下拉菜单

- **输入示例（数据）**：
```php
XfAdmin::dropdown([
    'text'    => '更多操作',
    'variant' => 'light',
    'items'   => [
        ['text' => '编辑', 'url' => '/edit', 'icon' => 'ti ti-pencil'],
        ['divider' => true],
        ['header' => '危险操作'],
        ['text' => '删除', 'url' => '/del', 'class' => 'text-danger'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `icon`

图标（Tabler webfont / Lucide SVG，模板内置两套图标库）

- **输入示例（数据）**：
```php
XfAdmin::icon(['name' => 'home'])                              // Tabler: ti ti-home
XfAdmin::icon(['name' => 'settings', 'lib' => 'lucide'])       // Lucide SVG（data-lucide）
XfAdmin::icon(['name' => 'bell', 'size' => 'fs-24', 'color' => 'text-primary'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `listGroup`

列表组

- **输入示例（数据）**：
```php
XfAdmin::listGroup([
    'items' => [
        ['text' => '项目一', 'active' => true],
        ['text' => '项目二', 'url' => '/x', 'badge' => ['text' => '3', 'class' => 'bg-danger']],
        ['content' => '<b>自定义HTML</b>'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `loadingButton`

加载/忙碌按钮（misc-loading-buttons）—— 点击后显示 spinner，避免重复提交

- **前端控件**：`data-xf="loading-btn"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::loadingButton([
    'text'    => '保存',
    'variant' => 'primary',
    'driver'  => 'spinner',   // spinner | ladda
    'type'    => 'submit',
    'size'    => '',          // lg | sm
])
```
- **输出**：带 `data-xf="loading-btn"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `modal`

模态框

- **输入示例（数据）**：
```php
XfAdmin::modal([
    'id'      => 'user-modal',
    'title'   => '编辑用户',
    'body'    => $form,
    'footer'  => '<button class="btn btn-light" data-bs-dismiss="modal">取消</button><button class="btn btn-primary">保存</button>',
    'size'    => 'lg',          // sm | lg | xl | fullscreen
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `offcanvas`

抽屉（Offcanvas）

- **输入示例（数据）**：
```php
XfAdmin::offcanvas([
    'id'        => 'filter-panel',
    'title'     => '筛选',
    'body'      => $form,
    'placement' => 'end',      // start | end | top | bottom
    'backdrop'  => true,
    'trigger'   => '打开筛选',
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `pagination`

分页

- **输入示例（数据）**：
```php
XfAdmin::pagination([
    'total'    => 200,        // 总条数
    'per_page' => 10,
    'current'  => 3,
    'url'      => '/users?page={page}',   // {page} 占位
    'size'     => null,       // sm | lg
    'align'    => 'center',   // start | center | end
    'rounded'  => false,
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `placeholder`

骨架占位（Bootstrap Placeholder）—— 加载态占位内容

- **输入示例（数据）**：
```php
XfAdmin::placeholder([
    'lines'   => [12, 6, 8, 12],   // 每行占用的栅格列数
    'glow'    => true,             // glow|wave 动画
    'animation' => 'glow',
    'variant' => null,            // primary/secondary...
    'size'    => null,            // xs|sm|lg
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `popover`

弹出框（Bootstrap Popover）

- **输入示例（数据）**：
```php
XfAdmin::popover([
    'text'      => '点击我',
    'title'     => '弹框标题',
    'content'   => '弹框正文内容',
    'placement' => 'right',
    'trigger'   => 'click',   // click|hover|focus
    'tag'       => 'button',
    'class'     => 'btn btn-primary',
    'html'      => false,
    'dismiss'   => false,     // true 时点击外部关闭
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `progress`

进度条（支持多段叠加）

- **输入示例（数据）**：
```php
XfAdmin::progress(['value' => 60, 'variant' => 'success', 'striped' => true, 'animated' => true, 'label' => '60%'])
XfAdmin::progress(['bars' => [['value' => 30, 'variant' => 'success'], ['value' => 20, 'variant' => 'warning']]])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `rating`

星级评分（纯展示，无外部依赖）

- **输入示例（数据）**：
```php
XfAdmin::rating([
    'value'   => 3.5,
    'max'     => 5,
    'variant' => 'warning',
    'size'    => null,          // fs-3 等
    'show_value' => true,
    'count'   => 128,           // 评价数
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `ratio`

响应式媒体容器（视频 / iframe / 图片，Bootstrap Ratio）

- **输入示例（数据）**：
```php
XfAdmin::ratio([
    'ratio'  => '16x9',              // 1x1|4x3|16x9|21x9 或自定义百分比数字
    'src'    => 'https://...',       // iframe/video 源
    'type'   => 'iframe',            // iframe|video|content
    'body'   => '自定义内容',
    'allowfullscreen' => true,
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `ribbon`

缎带角标（用于卡片角落标记）

- **输入示例（数据）**：
```php
XfAdmin::ribbon([
    'text'    => '推荐',
    'variant' => 'danger',
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `scrollspy`

滚动监听（Bootstrap Scrollspy）

- **输入示例（数据）**：
```php
XfAdmin::scrollspy([
    'items'  => [
        ['id' => 'sec1', 'label' => '第一节', 'content' => '...'],
        ['id' => 'sec2', 'label' => '第二节', 'content' => '...'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `spinner`

加载指示器（Bootstrap spinner / SpinKit 高级动画）

- **输入示例（数据）**：
```php
XfAdmin::spinner(['type' => 'border', 'variant' => 'primary', 'size' => 'sm'])
XfAdmin::spinner(['spinkit' => 'wave'])   // plane|chase|bounce|wave|pulse|flow|swing|circle|circle-fade|grid|fold|wander
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `statCard`

数据统计卡片（仪表盘小部件）

- **前端控件**：`data-xf="counter"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::statCard([
    'title'   => '总用户',
    'value'   => '12,480',
    'icon'    => 'ti ti-users',
    'variant' => 'primary',
    'trend'   => ['text' => '+12.5%', 'direction' => 'up', 'label' => '较上周'],
    'url'     => '/users',
    'counter' => 12480,   // 传数字启用数字滚动动画
])
```
- **输出**：带 `data-xf="counter"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `stepper`

步骤条（只读进度展示，如订单进度 ecommerce-order-details.html）

- **输入示例（数据）**：
```php
XfAdmin::stepper([
    'steps' => [
        ['title' => '已下单', 'text' => '10:00', 'status' => 'done'],
        ['title' => '已发货', 'text' => '12:00', 'status' => 'active'],
        ['title' => '已签收', 'status' => 'pending'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `tabs`

选项卡（tabs / pills / 垂直 / 图标 / 淡入动画）

- **输入示例（数据）**：
```php
XfAdmin::tabs([
    'style' => 'tabs',                 // tabs | pills | underline
    'items' => [
        ['title' => '基本信息', 'icon' => 'ti ti-home', 'content' => '...', 'active' => true],
        ['title' => '安全设置', 'content' => $form],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `timeline`

时间线

- **输入示例（数据）**：
```php
XfAdmin::timeline([
    'items' => [
        ['time' => '09:30', 'title' => '创建订单', 'text' => '订单 #1234 已创建', 'icon' => 'ti ti-plus', 'variant' => 'primary'],
        ['time' => '10:00', 'title' => '已付款', 'variant' => 'success'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `toast`

轻提示（Toast）

- **输入示例（数据）**：
```php
XfAdmin::toast(['title' => '通知', 'body' => '保存成功', 'variant' => 'success', 'autohide' => true, 'show' => true])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `tooltip`

文字提示（Bootstrap Tooltip）

- **输入示例（数据）**：
```php
XfAdmin::tooltip([
    'text'      => '按钮文字',           // 触发元素内容（HTML）
    'title'     => '这是提示内容',
    'placement' => 'top',               // top|bottom|left|right
    'tag'       => 'button',            // 触发元素标签
    'class'     => 'btn btn-primary',
    'html'      => false,               // title 是否允许 HTML
    'trigger'   => null,               // hover focus click
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。


## 业务/数据组件

### `activityFeed`

动态/活动流（project-activity.html）

- **输入示例（数据）**：
```php
XfAdmin::activityFeed([
    'items' => [
        ['avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'action' => '评论了任务',
         'target' => '首页改版', 'time' => '2 小时前', 'text' => '看起来不错'],
        ['icon' => 'ti ti-check', 'variant' => 'success', 'user' => '系统',
         'action' => '完成部署', 'time' => '昨天'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `apiKeys`

API 密钥管理（api-keys.html）—— 列表展示、复制、显示/隐藏、重新生成

- **前端控件**：`data-xf="copy"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::apiKeys([
    'items' => [
        ['name' => '生产环境', 'key' => 'sk-live-xxxx', 'created' => '2026-01-01', 'last_used' => '2天前'],
```
- **输出**：带 `data-xf="copy"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `blogList`

博客文章列表（blog.html / blog-details.html）—— 卡片网格或列表视图

- **输入示例（数据）**：
```php
XfAdmin::blogList([
    'items' => [
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `chatBox`

聊天窗口（chat.html）

- **前端控件**：`data-xf="chat-scroll"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::chatBox([
    'title'    => '张三',
    'status'   => '在线',
    'avatar'   => 'users/avatar-2.jpg',
    'height'   => '460px',
    'messages' => [
        ['from' => 'them', 'text' => '你好', 'time' => '10:00', 'avatar' => 'users/avatar-2.jpg'],
        ['from' => 'me',   'text' => '在的', 'time' => '10:01'],
```
- **输出**：带 `data-xf="chat-scroll"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `commentThread`

评论/讨论线程（article.html / forum-post.html）—— 支持嵌套回复

- **前端控件**：`data-xf="comment-form"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::commentThread([
    'items' => [
```
- **输出**：带 `data-xf="comment-form"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `emailCompose`

邮件撰写（email-compose.html）—— 收件人/主题 + 富文本正文

- **输入示例（数据）**：
```php
XfAdmin::emailCompose([
    'to'       => '', 'subject' => '', 'body' => '',
    'action'   => '/mail/send',
    'editor'   => 'quill',   // quill | textarea
])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `faq`

常见问题（pages-faq.html）—— 手风琴样式

- **输入示例（数据）**：
```php
XfAdmin::faq([
    'items' => [
        ['q' => '如何注册？', 'a' => '点击右上角注册按钮...'],
        ['q' => '如何退款？', 'a' => '联系客服...'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `fileManager`

文件管理器网格（file-manager.html）

- **输入示例（数据）**：
```php
XfAdmin::fileManager([
    'files' => [
        ['name' => '文档.pdf', 'type' => 'pdf', 'size' => '2.4 MB', 'meta' => '3 天前', 'href' => '#'],
        ['name' => '图片', 'type' => 'folder', 'meta' => '32 个文件'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `gallery`

图片画廊 / 作品集（pages-gallery.html）—— 支持 masonry 瀑布流与 lightbox 灯箱

- **前端控件**：`data-xf="lightbox"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::gallery([
    'items' => [
        ['src' => 'images/gallery/1.jpg', 'thumb' => 'images/gallery/1.jpg', 'title' => '项目A', 'caption' => '说明', 'group' => 'design'],
        ['src' => 'images/gallery/2.jpg', 'title' => '项目B', 'group' => 'photo'],
```
- **输出**：带 `data-xf="lightbox"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `invoiceList`

发票列表（invoice.html）—— 表格形式呈现多张发票，含状态、金额、操作

- **输入示例（数据）**：
```php
XfAdmin::invoiceList([
    'items' => [
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `invoiceTable`

发票明细表（invoice-details.html）—— 含合计区

- **输入示例（数据）**：
```php
XfAdmin::invoiceTable([
    'items' => [
        ['name' => '产品A', 'desc' => '说明', 'qty' => 2, 'price' => 100, 'total' => 200],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `kanban`

看板（project-kanban.html）—— 支持拖拽排序（SortableJS）

- **前端控件**：`data-xf="kanban"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::kanban([
    'columns' => [
```
- **输出**：带 `data-xf="kanban"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `mailList`

邮件列表（email.html）

- **输入示例（数据）**：
```php
XfAdmin::mailList([
    'items' => [
        ['from' => '张三', 'avatar' => 'users/avatar-1.jpg', 'subject' => '会议通知',
         'excerpt' => '明天下午三点...', 'time' => '10:30', 'unread' => true,
         'starred' => false, 'label' => ['text'=>'工作','variant'=>'primary'], 'href' => '#'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `permissionMatrix`

权限矩阵（roles.html / permissions.html）—— 角色 × 权限 的勾选网格

- **输入示例（数据）**：
```php
XfAdmin::permissionMatrix([
    'roles' => ['admin' => '管理员', 'editor' => '编辑'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `pricingCard`

定价卡片（pages-pricing.html）

- **输入示例（数据）**：
```php
XfAdmin::pricingCard([
    'name'     => '专业版',
    'price'    => '¥199',
    'period'   => '/ 月',
    'desc'     => '适合成长中的团队',
    'features' => [
        ['text' => '10 个项目', 'enabled' => true],
        ['text' => 'API 访问', 'enabled' => false],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `productCard`

商品卡片（ecommerce-products-grid.html）

- **输入示例（数据）**：
```php
XfAdmin::productCard([
    'image'    => 'products/1.png',
    'title'    => '男士运动鞋',
    'category' => '鞋类',
    'price'    => '¥299',
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `profileHeader`

个人资料头部（pages-profile.html / ecommerce-seller-details.html）

- **输入示例（数据）**：
```php
XfAdmin::profileHeader([
    'cover'   => 'small/img-10.jpg',
    'avatar'  => 'users/avatar-1.jpg',
    'name'    => '张三',
    'role'    => '前端工程师',
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `searchResults`

搜索结果列表（pages-search-results.html）

- **输入示例（数据）**：
```php
XfAdmin::searchResults([
    'query'  => 'laravel',
    'count'  => 12,
    'items'  => [
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `widget`

仪表盘小部件（widgets.html / index.html）—— 多种预设样式

- **输入示例（数据）**：
```php
XfAdmin::widget([
    'style'   => 'icon',          // icon|progress|chart|minimal
    'title'   => '总营收',
    'value'   => '¥52,000',
    'icon'    => 'ti ti-currency-yen',
    'variant' => 'primary',
    'trend'   => ['value' => '8.2%', 'up' => true, 'text' => '较上周'],
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。


## 杂项组件

### `calendar`

日历（FullCalendar）

- **前端控件**：`data-xf="calendar"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::calendar([
    'events' => [['title' => '会议', 'start' => '2026-07-23', 'className' => 'bg-primary']],
    'editable' => true,
    'options'  => [ ...透传 FullCalendar 配置... ],
])
```
- **输出**：带 `data-xf="calendar"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `clipboard`（别名：`clipboard`, `clipboardButton`）

复制按钮（clipboard.js）

- **前端控件**：`data-xf="clipboard"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::clipboard(['text' => '要复制的内容', 'label' => '复制'])
XfAdmin::clipboard(['target' => '#code-block', 'label' => '复制代码'])
```
- **输出**：带 `data-xf="clipboard"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `idleTimer`

空闲计时器（misc-idle-timer）—— 用户无操作超时后触发回调（如弹出登录框/提示）

- **前端控件**：`data-xf="idle-timer"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::idleTimer([
    'timeout'  => 60,           // 秒
    'onIdle'   => 'alert("您已离开一会儿")',  // 客户端回调（谨慎使用，建议用 onIdleUrl）
```
- **输出**：带 `data-xf="idle-timer"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `lightbox`

图片画廊 / 灯箱（GLightbox，可选 Masonry 瀑布流布局）

- **前端控件**：`data-xf="lightbox"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::lightbox([
    'images' => [
        ['src' => '/big1.jpg', 'thumb' => '/small1.jpg', 'title' => '图一'],
```
- **输出**：带 `data-xf="lightbox"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `nestable`

可拖拽排序列表（SortableJS，支持跨列表拖拽、把手、嵌套）

- **前端控件**：`data-xf="sortable"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::nestable([
    'items'  => ['项目一', '项目二', ['content' => '<b>自定义</b>', 'id' => 5]],
    'group'  => 'shared',       // 同组列表可互相拖拽
    'handle' => false,          // true 时渲染拖拽把手
    'input'  => 'sort_order',   // 排序结果同步到隐藏 input（逗号分隔 id）
])
```
- **输出**：带 `data-xf="sortable"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `pdfViewer`

PDF 查看器（misc-pdf-viewer）—— 使用本地 pdf.js 渲染，完全离线

- **输入示例（数据）**：
```php
XfAdmin::pdfViewer([
    'url'    => '/files/doc.pdf',
    'height' => 600,
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `raw`

原样输出（用于把任意 HTML 混入组件树，同时可声明所需插件资源）

- **输入示例（数据）**：
```php
XfAdmin::raw(['html' => '<div id="custom"></div>', 'plugins' => ['apexcharts'], 'js' => 'console.log("init")'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `sweetAlert`

SweetAlert2 弹窗（确认框 / 成功提示等） // 按钮触发确认框（confirm_url 确认后跳转 / confirm_js 执行自定义 JS）

- **前端控件**：`data-xf="sweetalert"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::sweetAlert([
    'trigger' => '删除',
    'trigger_variant' => 'danger',
    'title'   => '确定删除？',
    'text'    => '删除后不可恢复',
    'icon'    => 'warning',
    'confirm_text' => '删除',
    'cancel_text'  => '取消',
    'confirm_url'  => '/users/1/delete',
])
// 页面加载即弹出：'auto' => true
```
- **输出**：带 `data-xf="sweetalert"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `textDiff`

文本对比（misc-text-diff）—— 基于本地 jsdiff 渲染行内/并排差异

- **前端控件**：`data-xf="diff"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::textDiff([
    'old' => $v1,
    'new' => $v2,
```
- **输出**：带 `data-xf="diff"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `tinycon`

浏览器标签角标通知（misc-live-favicon）—— 在 favicon 上显示未读数量

- **输入示例（数据）**：
```php
XfAdmin::tinycon(['count' => 5, 'color' => '#e63757'])
```
- **输出**：纯服务端 HTML 片段（无 JS 行为，可直接嵌入 `page()` 的 `content`）。
- **输入参数**：见组件文档注释 / 上方示例。

### `tour`

新手引导（TourGuide JS）

- **前端控件**：`data-xf="tour"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::tour([
    'steps' => [
        ['target' => '#menu', 'title' => '导航菜单', 'content' => '在这里切换功能模块'],
        ['target' => '#search', 'title' => '搜索', 'content' => '全局搜索'],
```
- **输出**：带 `data-xf="tour"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

### `treeView`

树形视图（jsTree，支持复选框、拖拽、无限层级）

- **前端控件**：`data-xf="jstree"`（由 `xfadmin.js` 或内联 `XFAdmin.register` 初始化）
- **输入示例（数据）**：
```php
XfAdmin::treeView([
    'data' => [
        ['text' => '根节点', 'state' => ['opened' => true], 'children' => [
            ['text' => '子节点1', 'icon' => 'ti ti-file'],
```
- **输出**：带 `data-xf="jstree"` 的根元素 + 自动注册对应插件资源（CSS/JS）与内联初始化脚本；交互行为由前端控件完成。
- **输入参数**：见组件文档注释 / 上方示例。

