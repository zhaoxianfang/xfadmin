# 业务组件（二）内容 · 社区 · 文件

> 文章、博客、评论、论坛、FAQ、画廊、文件管理、搜索结果、条款与隐私政策。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`article`](#article) — 文章详情（通用内容页）
- [`blogArticle`](#blogarticle) — 博客文章详情（正文+作者+评论）
- [`blogList`](#bloglist) — 博客列表（文章卡片流）
- [`commentThread`](#commentthread) — 评论线程（嵌套回复列表）
- [`faq`](#faq) — 常见问题（问答卡片列表）
- [`faqAccordion`](#faqaccordion) — FAQ 手风琴（折叠问答）
- [`forumThread`](#forumthread) — 论坛帖子（主题+回复线程）
- [`gallery`](#gallery) — 图片画廊（网格+灯箱）
- [`terms`](#terms) — 服务条款页（长文本条款）
- [`privacyPolicy`](#privacypolicy) — 隐私政策页（长文本）
- [`sitemap`](#sitemap) — 站点地图（层级链接导航）
- [`socialFeed`](#socialfeed) — 社交动态流（朋友圈式信息流）
- [`fileManager`](#filemanager) — 文件管理器（目录/文件网格）
- [`searchResults`](#searchresults) — 搜索结果列表（标题+摘要+链接）
- [`searchResultsRich`](#searchresultsrich) — 富搜索结果（分组+缩略图）
- [`pricingCard`](#pricingcard) — 价格方案卡（推荐标记/功能列表/按钮）
- [`testimonial`](#testimonial) — 用户证言（头像+评语+星标）

## 本章导读

内容与社区类组件：文章/博客/评论/论坛/FAQ/画廊/文件管理/搜索结果。

### 组合范式

```php
echo XfAdmin::blogList(['posts' => \$posts]);
echo XfAdmin::commentThread(['comments' => \$comments, 'action' => '/comments']);
echo XfAdmin::gallery(['items' => [['image' => 'gallery/1.jpg', 'title' => '...']]]);
echo XfAdmin::fileManager(['folders' => [...], 'files' => [...]]);
```

### 约定与陷阱

- `CommentThread`、`Gallery` 带**自带内联 JS**（评论表单、画廊搜索），勿重复初始化；
- 长文本组件（`terms`、`privacyPolicy`、`article`）的内容槽位为 `raw()`，
  若内容来自用户输入需自行转义；
- `Gallery` 依赖 lightbox 时会自动加载 glightbox 资源。


---

### `article`

文章详情（通用内容页）。

> **类**：`zxf\XfAdmin\Components\Data\Article`
> **文件**：`src/Components/Data/Article.php`（109 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::article([
    'article' => [
        'title'    => '如何构建高可用后台系统',
        'category' => '架构', 'date' => '2026-07-20', 'read_time' => '8 分钟',
        'author'   => ['name' => '张三', 'avatar' => 'users/user-1.jpg', 'bio' => '资深后端工程师'],
        'cover'    => 'blog/blog-post.jpg',
        'body'     => ['第一段正文……', '第二段正文……'],
        'quote'    => '架构的优雅在于取舍。',
        'tags'     => ['PHP', 'Laravel', '架构'],
        'related'  => [['title' => '相关文章一', 'excerpt' => '摘要……', 'image' => 'blog/blog-1.jpg', 'date' => '2026-07-10']],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `article` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `blogArticle`

博客文章详情（正文+作者+评论）。

> **类**：`zxf\XfAdmin\Components\Data\BlogArticle`
> **文件**：`src/Components/Data/BlogArticle.php`（69 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::blogArticle([
    'article' => [
        'title' => '…', 'category' => '技术', 'date' => '2026-07-20', 'read_time' => '8 分钟',
        'author' => ['name'=>'张三','avatar'=>'users/user-1.jpg','bio'=>'…'],
        'cover' => 'gallery/12.jpg',
        'body' => ['段落一…','段落二…'],
        'tags' => ['PHP','Laravel'],
        'related' => [['title'=>..,'excerpt'=>..,'image'=>..,'date'=>..]],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `article` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `blogList`

博客列表（文章卡片流）。

> **类**：`zxf\XfAdmin\Components\Data\BlogList`
> **文件**：`src/Components/Data/BlogList.php`（166 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::blogList([
    'items' => [
        [
            'image'   => 'images/blog/blog-1.jpg',
            'category'=> '技术',
            'title'   => '如何构建后台系统',
            'excerpt' => '本文介绍……',
            'author'  => ['name' => '张三', 'avatar' => 'users/user-2.jpg'],
            'date'    => '2026-07-01',
            'comments'=> 12,
            'views'   => 320,
            'url'     => '/blog/1',
            'tags'    => ['Laravel', '后台'],
        ],
    ],
    'layout' => 'grid',   // grid | list
    'cols'   => 3,
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `layout` | string | `'grid'` | 布局模式（各组件不同，如 vertical/horizontal） |
| `cols` | int | `3` | 列数（栅格 / 分区列数） |
| `gap` | string | `'24px'` | 间距 |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `commentThread`

评论线程（嵌套回复列表）。

> **类**：`zxf\XfAdmin\Components\Data\CommentThread`
> **文件**：`src/Components/Data/CommentThread.php`（106 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::commentThread([
    'items' => [
        [
            'avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'time' => '2小时前',
            'text' => '写得很好', 'likes' => 3,
            'replies' => [ ['avatar'=>'users/avatar-2.jpg','user'=>'李四','time'=>'1小时前','text'=>'同意'] ],
        ],
    ],
    'form' => true,   // 顶部发表框
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `form` | bool | `true` |  |
| `maxDepth` | int | `4` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `faq`

常见问题（问答卡片列表）。

> **类**：`zxf\XfAdmin\Components\Data\Faq`
> **文件**：`src/Components/Data/Faq.php`（51 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::faq([
    'items' => [
        ['q' => '如何注册？', 'a' => '点击右上角注册按钮...'],
        ['q' => '如何退款？', 'a' => '联系客服...'],
    ],
    'flush'      => false,
    'bordered'   => true,
    'open'       => 0,          // 默认展开第几个，null 表示全部收起
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `flush` | bool | `false` |  |
| `bordered` | bool | `true` | 是否显示边框 |
| `open` | int | `0` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `faqAccordion`

FAQ 手风琴（折叠问答）。

> **类**：`zxf\XfAdmin\Components\Data\FaqAccordion`
> **文件**：`src/Components/Data/FaqAccordion.php`（53 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::faqAccordion([
    'items' => [
        ['q' => '如何重置密码？', 'a' => '在登录页点击「忘记密码」，按邮件指引操作即可。'],
        ['q' => '支持哪些支付方式？', 'a' => '支持微信、支付宝及对公转账。'],
    ],
    'title' => '常见问题',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `title` | string | `'常见问题'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `forumThread`

论坛帖子（主题+回复线程）。

> **类**：`zxf\XfAdmin\Components\Data\ForumThread`
> **文件**：`src/Components/Data/ForumThread.php`（88 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::forumThread([
    'thread' => ['title'=>'如何优化 Laravel 性能？','category'=>'技术问答','author'=>['name'=>'张三','avatar'=>'users/user-1.jpg'],
                 'views'=>320,'replies'=>3,'created_at'=>'2026-07-20','body'=>'…','tags'=>['PHP','Laravel']],
    'posts' => [
        ['author'=>['name'=>'李四','avatar'=>'users/user-2.jpg','role'=>'版主'],'created_at'=>'2026-07-21',
         'body'=>'…','likes'=>12,'is_solution'=>true,'attachments'=>[['name'=>'code.php']]],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `thread` | array | `[]` |  |
| `posts` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `gallery`

图片画廊（网格+灯箱）。

> **类**：`zxf\XfAdmin\Components\Data\Gallery`
> **文件**：`src/Components/Data/Gallery.php`（182 行）
> **依赖插件**：`masonry`、`glightbox`

**用法示例**

```php
XfAdmin::gallery([
    'items' => [
        ['src' => 'gallery/1.jpg', 'title' => '项目A', 'caption' => '说明', 'group' => 'design'],
        ['src' => 'gallery/2.jpg', 'title' => '项目B', 'group' => 'photo'],
    ],
    'filter'   => ['all' => '全部', 'design' => '设计', 'photo' => '摄影'], // 分类筛选按钮（键 all 表示全部）
    'search'   => true,        // 卡片头部搜索框（按标题/说明/分类实时过滤，联动筛选按钮）
    'masonry'  => true,        // 瀑布流布局（保留图片原始比例；需 masonry 插件）
    'lightbox' => true,        // 点击放大灯箱（glightbox）
    'cols'     => 4,           // 桌面端列数（映射为 INSPINIA 同款 row-cols-* 响应式组合）
    'ratio'    => '4x3',       // 非 masonry 模式下缩略图裁切比例（如 1x1 / 4x3 / 16x9）
    'card'     => true,        // 是否输出外层卡片容器（嵌入已有卡片时可关闭）
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `masonry` | bool | `true` |  |
| `lightbox` | bool | `true` |  |
| `search` | bool | `true` | 是否启用搜索 |
| `cols` | int | `4` | 列数（栅格 / 分区列数） |
| `ratio` | string | `'4x3'` |  |
| `gap` | string | `''` | 兼容旧参数；留空时用 INSPINIA 的 g-2 间距 |
| `filter` | array | `[]` | ['all' => '全部', 'design' => '设计', ...] |
| `card` | bool | `true` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `terms`

服务条款页（长文本条款）。

> **类**：`zxf\XfAdmin\Components\Data\Terms`
> **文件**：`src/Components/Data/Terms.php`（79 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::terms([
    'title'      => '服务条款',
    'updated_at' => '2026-07-01',              // 最近更新时间（可选）
    'intro'      => '<p>欢迎使用本服务…</p>',    // 前言 HTML（可选）
    'sections'   => [                          // 分节内容
        ['id' => 'usage', 'title' => '1. 使用规范', 'content' => '<p>…</p>'],
        ['id' => 'privacy', 'title' => '2. 隐私保护', 'content' => '<p>…</p>'],
    ],
    'toc'        => true,                      // 是否显示侧栏目录
    'accept'     => null,                      // 底部「同意」按钮：['label'=>'我已阅读并同意','url'=>..] 或 HTML
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'服务条款'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `updated_at` | mixed | `null` |  |
| `intro` | mixed | `null` |  |
| `sections` | array | `[]` | 分区数组 |
| `toc` | bool | `true` |  |
| `accept` | mixed | `null` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `privacyPolicy`

隐私政策页（长文本）。

> **类**：`zxf\XfAdmin\Components\Data\PrivacyPolicy`
> **文件**：`src/Components/Data/PrivacyPolicy.php`（87 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::privacyPolicy([
    'title' => 'Privacy Policy',
    'effectiveDate' => 'April 19, 2025',
    'intro' => 'This Privacy Policy explains how we collect...',
    'sections' => [
        ['title' => '1. Information We Collect', 'body' => '<p>We may collect personal details such as...</p>'],
        ['title' => '2. How We Use Your Information', 'body' => '<p>Your information is used to provide...</p>'],
        ...
    ],
    'contactEmail' => 'privacy@example.com',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'Privacy Policy'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `effectiveDate` | string | `''` |  |
| `intro` | string | `''` |  |
| `sections` | array | `[]` | 分区数组 |
| `contactEmail` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `sitemap`

站点地图（层级链接导航）。

> **类**：`zxf\XfAdmin\Components\Data\Sitemap`
> **文件**：`src/Components/Data/Sitemap.php`（91 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::sitemap([
    'columns' => [
        ['title' => 'Dashboard & Pages', 'links' => [
            ['label' => 'Dashboards', 'children' => ['Analytics', 'CRM', 'Sales', 'Minimal', 'eCommerce']],
            ['label' => 'Profile', 'children' => ['Overview', 'Edit', 'Security']],
            ['label' => 'Help Center'],
            ['label' => 'Login'],
            ['label' => 'Register'],
        ]],
        ['title' => 'Applications', 'links' => [
            ['label' => 'Calendar', 'icon' => 'ti-calendar'],
            ['label' => 'Email', 'icon' => 'ti-mail', 'children' => ['Inbox', 'Read', 'Compose']],
            ...
        ]],
        ...
    ],
    'colClass' => 'col-md-4',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `colClass` | string | `'col-md-4'` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `socialFeed`

社交动态流（朋友圈式信息流）。

> **类**：`zxf\XfAdmin\Components\Data\SocialFeed`
> **文件**：`src/Components/Data/SocialFeed.php`（68 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::socialFeed([
    'posts' => [
        [
            'avatar'   => 'users/user-1.jpg',
            'name'     => '林晓',
            'handle'   => '@linxiao',
            'time'     => '2 分钟前',
            'text'     => '刚刚上线了全新的数据看板，体验丝滑！',
            'image'    => 'products/1.png',
            'likes'    => 128,
            'comments' => 24,
            'shares'   => 8,
        ],
        [
            'avatar' => 'users/user-2.jpg',
            'name'   => '陈昊',
            'time'   => '15 分钟前',
            'text'   => '分享一篇关于数据驱动运营的干货文章。',
        ],
    ],
    'title' => '团队动态',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `posts` | array | `[]` |  |
| `title` | string | `'团队动态'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `fileManager`

文件管理器（目录/文件网格）。

> **类**：`zxf\XfAdmin\Components\Data\FileManager`
> **文件**：`src/Components/Data/FileManager.php`（96 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::fileManager([
    'files' => [
        ['name' => '文档.pdf', 'type' => 'pdf', 'size' => '2.4 MB', 'meta' => '3 天前', 'href' => '#'],
        ['name' => '图片', 'type' => 'folder', 'meta' => '32 个文件'],
    ],
    'cols' => ['md' => 3, 'sm' => 6],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `files` | array | `[]` |  |
| `cols` | array | `['md'=>3, 'sm'=>6]` | 列数（栅格 / 分区列数） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `searchResults`

搜索结果列表（标题+摘要+链接）。

> **类**：`zxf\XfAdmin\Components\Data\SearchResults`
> **文件**：`src/Components/Data/SearchResults.php`（95 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::searchResults([
    'query'  => 'laravel',
    'count'  => 12,
    'items'  => [
        [
            'icon'    => 'ti ti-folder',        // 或 thumb 图片
            'title'   => '文档中心',
            'url'     => '/docs',
            'excerpt' => '关于 Laravel 的文档……',
            'meta'    => '更新于 2026-07-01',
            'tags'    => ['Laravel', '文档'],
        ],
    ],
    'filters' => ['全部' => 12, '文档' => 8, '用户' => 4],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `query` | string | `''` |  |
| `count` | int | `0` | 数量 / 计数徽标数字 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `filters` | array | `[]` | 过滤条件定义 |
| `pagination` | string | `''` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `searchResultsRich`

富搜索结果（分组+缩略图）。

> **类**：`zxf\XfAdmin\Components\Data\SearchResultsRich`
> **文件**：`src/Components/Data/SearchResultsRich.php`（59 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::searchResultsRich([
    'query'   => '订单',
    'groups'  => [
        ['title'=>'订单','items'=>[['title'=>'订单 #1024','url'=>'#','desc'=>'...','tag'=>'订单'],['title'=>'...']]],
        ['title'=>'客户','items'=>[...]],
    ],
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `query` | string | `''` |  |
| `groups` | array | `[]` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `pricingCard`

价格方案卡（推荐标记/功能列表/按钮）。

> **类**：`zxf\XfAdmin\Components\Data\PricingCard`
> **文件**：`src/Components/Data/PricingCard.php`（68 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::pricingCard([
    'name'     => '专业版',
    'price'    => '¥199',
    'period'   => '/ 月',
    'desc'     => '适合成长中的团队',
    'features' => [
        ['text' => '10 个项目', 'enabled' => true],
        ['text' => 'API 访问', 'enabled' => false],
    ],
    'featured' => true,          // 高亮推荐
    'badge'    => '最受欢迎',
    'button'   => ['label' => '立即选购', 'href' => '#', 'variant' => 'primary'],
    'icon'     => 'ti ti-rocket',
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `name` | string | `''` | 表单字段名 / 语义名称 |
| `price` | string | `''` | 价格数值 |
| `period` | string | `'/ 月'` |  |
| `desc` | mixed | `null` |  |
| `features` | array | `[]` |  |
| `featured` | bool | `false` |  |
| `badge` | mixed | `null` | 徽标文本或 `['text'=>..,'class'=>..]` |
| `icon` | mixed | `null` | Tabler 图标 class，如 `ti ti-user` |
| `button` | array | `['label'=>'选择方案', 'href'=>'#', 'variant'=>'primary']` | 数组结构（见组件用法示例） |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

---

### `testimonial`

用户证言（头像+评语+星标）。

> **类**：`zxf\XfAdmin\Components\Data\Testimonial`
> **文件**：`src/Components/Data/Testimonial.php`（87 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::testimonial([
    'items' => [
        ['avatar' => 'users/avatar-2.jpg', 'name' => '李四', 'role' => 'CTO',
         'text' => '这套后台极大提升了我们的运营效率。', 'rating' => 5],
    ],
    'cols'     => 3,           // 网格列数（非 carousel 模式）
    'carousel' => false,       // true 时使用 Bootstrap 走马灯
]);
```

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `cols` | int | `3` | 列数（栅格 / 分区列数） |
| `carousel` | bool | `false` |  |

> 参数说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value` 等）见各组件所属基类的公共字段说明。

