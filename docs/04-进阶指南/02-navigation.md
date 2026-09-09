# 导航系统

XfAdmin 提供三套导航：**Sidenav**（垂直侧边栏）、**TopNav**（水平顶部导航）、
**Menu**（菜单 DSL，Sidenav 内部使用）。

## 1. 菜单数据结构（DSL）

菜单项支持的键（Sidenav 的 `Menu` 与 TopNav 通用，差异见各节）：

| 键 | 类型 | 说明 |
|---|---|---|
| `text` | string | 菜单文本 |
| `title` | string | 无 `text` 时作为**分组标题**（`side-nav-title` / `dropdown-header`） |
| `icon` | string | Tabler 图标 class，如 `ti ti-users` |
| `url` | string | 叶子节点链接（默认 `#!`） |
| `href` | string | `url` 的别名（`Menu` 中归一化） |
| `label` | string | `text` 的别名（`Menu` 中归一化） |
| `target` | string | `<a target>` |
| `badge` | string\|array | 徽标：`'New'` 或 `['text'=>'New','class'=>'bg-danger','pill'=>true]` |
| `disabled` | bool | 禁用 |
| `active` | bool | 强制激活；否则按 `url` 与 `current_url` 比对 |
| `children` | array | 子菜单（**无限级**） |
| `mega` | array | Mega 面板（**仅 TopNav**） |
| `divider` | bool | 下拉中的分隔线（TopNav） |
| `id` | string | 指定 collapse 容器 id（Sidenav） |
| `key` | string | 语义标识（保留） |

`Menu::normalizeItems()` 归一化规则（递归）：

- `label` → `text`；
- `title` → `text`（**仅当该项同时带 `url`/`href`/`children`/`icon`**，否则保留为分组标题）；
- `href` → `url`。

## 2. 完整示例

```php
$menu = [
    ['title' => '主导航'],                                    // 分组标题
    [
        'text'  => '仪表盘',
        'icon'  => 'ti ti-dashboard',
        'url'   => '/admin',
        'badge' => ['text' => 'Hot', 'class' => 'bg-danger'],
    ],
    [
        'text'     => '用户管理',
        'icon'     => 'ti ti-users',
        'children' => [
            ['text' => '用户列表', 'url' => '/admin/users'],
            ['text' => '角色权限', 'url' => '/admin/roles', 'badge' => 'New'],
            [
                'text'     => '高级',
                'children' => [                               // 三级
                    ['text' => '操作日志', 'url' => '/admin/logs'],
                    ['text' => '登录日志', 'url' => '/admin/logins'],
                ],
            ],
        ],
    ],
    ['text' => '已禁用', 'icon' => 'ti ti-ban', 'url' => '#!', 'disabled' => true],
];
```

## 3. Sidenav（垂直侧边栏）

### 3.1 参数

| 参数 | 默认 | 说明 |
|---|---|---|
| `brand` | `[]` | 与 `setting('brand')` 合并（`+=` 配置兜底） |
| `user` | `false` | 用户卡片；假值不渲染 |
| `menu` | `[]` | 数组 → `Menu::make(['mode' => 'side'])`；`Menu` 实例直接渲染；其余 `raw()` |
| `current_url` | `null` | 自动高亮 |
| `append` | `null` | 菜单下方附加内容（`raw`） |

`brand` 子键：`url`/`home_url`（默认 `/`）、`name`（默认 `XfAdmin`）、
`logo`（`images/logo.png`）、`logo_dark`（`images/logo-black.png`）、`logo_sm`（`images/logo-sm.png`）。

`user` 子键：`name`、`role`、`avatar`（默认 `images/users/user-2.jpg`）、
`url`（默认 `#!`）、`items[]`（`text`/`url`/`icon`/`class`/`divider`）。

### 3.2 用法

```php
XfAdmin::page([
    'menu'        => $menu,
    'current_url' => request()->path(),       // 自动高亮
    'sidenav'     => [
        'brand' => ['name' => '我的后台', 'logo' => 'my-logo.png'],
        'user'  => [
            'name'   => '张三',
            'role'   => '超级管理员',
            'avatar' => 'users/user-2.jpg',
            'items'  => [
                ['text' => '个人资料', 'url' => '/profile', 'icon' => 'ti ti-user'],
                ['divider' => true],
                ['text' => '退出', 'url' => '/logout', 'icon' => 'ti ti-logout'],
            ],
        ],
    ],
]);
```

### 3.3 输出结构

```html
<div class="sidenav-menu">
  <a href="/" class="logo">
    <span class="logo-light"><span class="logo-lg">…</span><span class="logo-sm">…</span></span>
    <span class="logo-dark">…</span>
  </a>
  <button class="button-on-hover"><i class="ti ti-menu-4"></i></button>
  <button class="button-close-offcanvas"><i class="ti ti-x"></i></button>
  <div class="scrollbar" data-simplebar>
    <div class="sidenav-user">…</div>
    <ul class="side-nav">…</ul>
    {append}
  </div>
</div>
```

菜单项结构：

```html
<li class="side-nav-title">分组</li>
<li class="side-nav-item [active]">
  <a class="side-nav-link [disabled] [active]" href="…"
     data-bs-toggle="collapse" href="#{cid}" aria-expanded aria-controls>
    <span class="menu-icon"><i class="ti ti-x"></i></span>
    <span class="menu-text">文本</span>
    <span class="badge …">徽标</span>
    <span class="menu-arrow"></span>
  </a>
  <div class="collapse [show]" id="{cid}">
    <ul class="sub-menu" data-level="2">…递归…</ul>
  </div>
</li>
```

- `data-level` 供 CSS 按层级递增缩进（支持 6 级以上）；
- 父级在任意后代激活时自动展开并加 `active`。

⚠️ **`Menu`（side 模式）不支持 `mega` 与 `permission`** —— 它们是 TopNav 的能力。
权限过滤请在生成 `$menu` 数组时自行处理。

## 4. Topbar（顶栏）

### 4.1 参数

| 参数 | 默认 | 说明 |
|---|---|---|
| `brand` | `true` | 顶栏 logo；数组时与 `setting('brand')` 合并 |
| `search` | `true` | 内联搜索框 |
| `search_placeholder` | `'Search...'` | |
| `search_modal` | `false` | 改为「搜索图标 + 全屏模态 `#xfTopbarSearchModal`」 |
| `search_hint` | — | 模态提示语（默认「输入关键字后回车进行搜索」） |
| `left` | `null` | 左侧附加插槽（`raw`） |
| `theme_toggle` | `true` | 明暗切换 `#light-dark-mode` |
| `fullscreen` | `true` | `[data-toggle="fullscreen"]` |
| `customizer` | `true` | 打开 `#theme-settings-offcanvas` |
| `languages` | `[]` | 语言切换 |
| `notifications` | `false` | 通知下拉 |
| `messages` | `false` | 消息下拉 |
| `apps` | `false` | 应用启动器九宫格 |
| `user` | `false` | 用户菜单 |
| `right` | `null` | 右侧最前附加插槽（`raw`） |

### 4.2 各模块配置

```php
'topbar' => [
    'search'   => true,
    'languages' => [
        ['flag' => 'flags/cn.svg', 'name' => '简体中文', 'code' => 'zh', 'url' => '/lang/zh', 'active' => true],
        ['flag' => 'flags/us.svg', 'name' => 'English',   'code' => 'en', 'url' => '/lang/en'],
    ],
    'notifications' => [
        'title'   => '通知',
        'count'   => 3,                       // 省略时取 items 数
        'items'   => [
            ['avatar' => 'users/user-1.jpg', 'title' => '新订单', 'text' => '订单 #12345', 'time' => '5 分钟前', 'url' => '/orders/1'],
            ['icon' => 'ti ti-bell', 'variant' => 'warning', 'title' => '库存预警', 'text' => '商品 A 库存不足', 'time' => '1 小时前'],
        ],
        'all_url' => '/notifications',
        'all_text' => '查看全部',
    ],
    'messages' => [
        'title' => '消息',
        'items' => [
            ['from' => '李四', 'text' => '你好', 'time' => '刚刚', 'unread' => true, 'url' => '#!'],
        ],
    ],
    'apps' => [
        'variant' => 'grid',                  // grid | rounded
        'title'   => '应用',
        'items'   => [
            ['icon' => 'ti ti-mail', 'text' => '邮箱', 'url' => '/mail', 'variant' => 'primary'],
            ['icon' => 'ti ti-calendar', 'text' => '日历', 'url' => '/cal'],
        ],
        'all_url' => '/apps', 'all_text' => '查看全部应用',
    ],
    'user' => [
        'avatar' => 'users/user-2.jpg',
        'name'   => '张三',
        'header' => '欢迎回来',
        'items'  => [
            ['text' => '个人资料', 'url' => '/profile', 'icon' => 'ti ti-user'],
            ['divider' => true],
            ['text' => '退出', 'url' => '/logout', 'icon' => 'ti ti-logout', 'class' => 'text-danger'],
        ],
    ],
];
```

### 4.3 渲染顺序

```
header.app-topbar > div.container-fluid.topbar-menu
  左: brand(logo-topbar) → button.sidenav-toggle-button → [搜索框 | 搜索模态] → {left}
  右: {right} → languages → messages → apps → notifications
      → customizer → fullscreen → theme_toggle → user
```

## 5. TopNav（水平导航）

> 源码：`src/Components/Layout/TopNav.php`（654 行）
> 来源：INSPINIA `layouts-horizontal.html` 的 `app-topbar` + `topnav` 合并为一个 `<header>`。

### 5.1 参数

| 参数 | 默认 | 说明 |
|---|---|---|
| `brand` | `true` | 与 `setting('brand')` 合并；额外支持 `name`/`title` → `span.logo-text` |
| `sidenav_toggle` | `false` | 纯水平布局默认不显示侧栏切换按钮 |
| `menu` | `[]` | 水平菜单数据 |
| `current_url` | `null` | 自动高亮 |
| `search` | `false` | 桌面 `d-none d-lg-flex` 搜索框 + 移动折叠面板内搜索框 |
| `search_placeholder` | `'Search for something...'` | |
| `mega` | `false` | 顶栏**独立** Mega 下拉 |
| `left` | `null` | 左侧插槽（`raw`） |
| `languages` | `[]` | 同 Topbar（带 `#selected-language-image` / `#selected-language-code`） |
| `messages` | `false` | 图标 `ti ti-mail`，徽标 `badge text-bg-success badge-circle`；支持 `label` |
| `notifications` | `false` | 图标 `ti ti-bell`，徽标 `badge bg-danger rounded-pill` |
| `theme_toggle` | `true` | |
| `fullscreen` | `true` | |
| `customizer` | `true` | |
| `user` | `false` | 见 5.4 |
| `apps` | `false` | **默认 `variant='rounded'`**（与 Topbar 相反） |
| `right` | `null` | 右侧插槽（`raw`） |

### 5.2 多级子菜单

```php
'menu' => [
    ['text' => '首页', 'icon' => 'ti ti-home', 'url' => '/'],
    [
        'text'     => '产品',
        'icon'     => 'ti ti-package',
        'children' => [
            ['text' => '全部产品', 'url' => '/products'],
            [
                'text'     => '分类',
                'children' => [
                    ['text' => '电子产品', 'url' => '/c/1', 'badge' => 'New'],
                    ['text' => '服饰', 'url' => '/c/2'],
                    ['divider' => true],
                    ['title' => '更多'],
                    ['text' => '家居', 'url' => '/c/3'],
                ],
            ],
        ],
    ],
];
```

- 支持**无限级**递归；桌面端靠 CSS `.topnav .dropdown:hover` 逐级浮出，
  空间不足时向左翻转（`xfadmin.js` 的 `placeMenu` 边界避让）；
- ⚠️ 一级菜单**刻意不使用** `data-bs-toggle="dropdown"`：
  交给 Bootstrap 会立即移除 `.show` 并施加浮层定位，破坏移动端点开逻辑；
- 移动端（<992px）由 `xfadmin.js` 切换 `.show`，呈垂直手风琴；
- 小屏折叠按钮：`.topnav-toggle-button`（`data-bs-target="#topnav-menu-content"`），始终渲染。

### 5.3 Mega Menu（两种写法）

**A. 菜单项内联 `mega`**

```php
[
    'text' => '解决方案',
    'icon' => 'ti ti-layout-grid',
    'mega' => [
        'cols'    => 4,                        // 夹紧 1~4
        'title'   => '全场景解决方案',
        'columns' => [
            ['title' => '电商', 'items' => [
                ['text' => '商城系统', 'url' => '/s1', 'icon' => 'ti ti-shopping-cart'],
                ['text' => '订单管理', 'url' => '/s2'],
            ]],
            ['title' => 'CRM', 'items' => [
                ['text' => '客户管理', 'url' => '/s3'],
                ['text' => '商机', 'url' => '/s4', 'badge' => 'Hot'],
            ]],
        ],
        'footer' => '<a href="/all" class="d-block text-center py-2">查看全部 →</a>',
    ],
]
```

**B. 顶栏独立 `mega`**（顶层参数）

```php
'mega' => [
    'text' => 'Mega Menu', 'cols' => 3, 'title' => '…', 'columns' => [...], 'footer' => '…',
],
```

容器：`div.dropdown-menu.dropdown-menu-xxl.p-0` → `div.h-100[style="max-height:380px"][data-simplebar]`。

### 5.4 `user`（TopNav 版）

```php
'user' => [
    'name'      => '张三',
    'role'      => '超级管理员',        // 回退 subtitle
    'signature' => '一句话签名（两行截断）',
    'avatar'    => 'users/user-2.jpg',
    'header'    => '账户',
    'items'     => [...],
],
```

下拉顶部固定渲染 `dropdown-header.noti-title.bg-primary-subtle.rounded-top` 用户卡。

### 5.5 完整示例

```php
echo XfAdmin::page([
    'title'  => '水平导航演示',
    'layout' => 'horizontal',
    'topnav' => [
        'menu'       => \zxf\XfAdmin\Support\DemoMenu::topNavMenu(),
        'current_url'=> '/admin',
        'search'     => true,
        'languages'  => [
            ['flag' => 'flags/cn.svg', 'name' => '简体中文', 'code' => 'zh', 'active' => true],
            ['flag' => 'flags/us.svg', 'name' => 'English', 'code' => 'en'],
        ],
        'notifications' => ['title' => '通知', 'items' => [...]],
        'user' => ['name' => '张三', 'avatar' => 'users/user-2.jpg', 'items' => [...]],
    ],
    'content' => $content,
]);
```

## 6. 菜单激活（高亮）逻辑

```php
$active = $item['active']                                  // 显式指定
    ?? (rtrim(ltrim($item['url'], '/'), '/') === rtrim(ltrim($current_url, '/'), '/'))
    || 任意后代激活;
```

因此在 `Page` 上传 `current_url` 即可自动高亮整条父链：

```php
'current_url' => request()->path(),      // Laravel
'current_url' => request()->url(),       // ThinkPHP
```

## 7. 权限过滤（推荐做法）

组件本身不处理权限，请在生成菜单数组时过滤：

```php
function buildMenu(array $menu, array $permissions): array
{
    $out = [];
    foreach ($menu as $item) {
        if (! empty($item['children'])) {
            $item['children'] = buildMenu($item['children'], $permissions);
            // 子项全被过滤掉且自身无 url → 不显示父项
            if ($item['children'] === [] && empty($item['url'])) continue;
        } elseif (! empty($item['permission']) && ! in_array($item['permission'], $permissions, true)) {
            continue;
        }
        $out[] = $item;
    }
    return $out;
}
```

## 8. 常见问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 菜单不展开 | 缺 Bootstrap JS，或 `app.js` 未执行 `initLeftSidebar` | 确认 `scripts()` 已输出 |
| 水平导航下拉不显示 | 用了 `data-bs-toggle="dropdown"`（会破坏 hover 级联） | 组件已刻意不用，勿自行添加 |
| TopNav 折叠按钮无效 | 自定义布局未引入 `xfadmin.js` | 确认 `scripts()` |
| 图标不显示 | 图标 class 非 Tabler（`ti ti-*`） | 统一用 Tabler，或改用 `lucide`（需自行注册） |
| 多级菜单缩进异常 | 自定义 CSS 覆盖了 `data-level` 规则 | 检查 `xfadmin.css` 是否被覆盖 |
| 当前菜单不高亮 | 未传 `current_url`，或 URL 格式不一致 | 传去除首尾 `/` 的路径 |
