# 业务组件（三）用户 · 组织 · 项目协作

> 客户/公司/联系人、角色权限、团队成员、项目与任务、看板、商机、用户档案与账户设置。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`clients`](#clients) — 客户/客户端列表（apps-clients）
- [`companies`](#companies) — 公司列表（apps-companies）
- [`companyCard`](#companycard) — 公司卡（logo/简介/链接）
- [`contactCard`](#contactcard) — 联系人卡（头像/电话/邮件）
- [`contactList`](#contactlist) — 联系人列表（通讯录）
- [`roles`](#roles) — 角色管理（角色列表+成员）
- [`permissionMatrix`](#permissionmatrix) — 权限矩阵（角色×权限勾选表）
- [`teamMember`](#teammember) — 团队成员卡（头像/职位/操作）
- [`projects`](#projects) — 项目列表（卡片/列表视图）
- [`projectDetails`](#projectdetails) — 项目详情（概览/进度/成员）
- [`projectActivity`](#projectactivity) — 项目活动流（动态时间线）
- [`projectTeamBoard`](#projectteamboard) — 项目团队看板（成员任务分配）
- [`taskList`](#tasklist) — 任务清单（勾选/优先级）
- [`todoList`](#todolist) — 待办列表（添加/完成）
- [`issueTracker`](#issuetracker) — 问题追踪（看板式缺陷管理）
- [`kanban`](#kanban) — 看板（拖拽列/卡片，项目与工单管理）
- [`deals`](#deals) — 交易/商机列表（CRM 看板式）
- [`userProfile`](#userprofile) — 用户资料卡（详情展示）
- [`profileHeader`](#profileheader) — 个人资料头部（封面+头像+统计）
- [`profilePage`](#profilepage) — 个人资料页（头部+标签页+动态）
- [`accountSettings`](#accountsettings) — 账户设置整页（tabs+表单）
- [`voteList`](#votelist) — 投票列表（选项+进度条）
- [`activityFeed`](#activityfeed) — 活动动态流（时间线式操作记录）

## 本章导读

用户、组织与项目协作类组件：客户/公司/联系人、角色权限、团队、项目任务、看板、商机、档案。

### 组合范式

```php
echo XfAdmin::kanban(['columns' => [...], 'cards' => [...]]);
echo XfAdmin::permissionMatrix(['roles' => \$roles, 'permissions' => \$perms, 'matrix' => \$matrix]);
echo XfAdmin::profilePage(['user' => \$user, 'tabs' => [...]]);
```

### 约定与陷阱

- `kanban` 拖拽需 SortableJS（自动加载），跨列移动会按 `data-xf-update-url` 持久化；
- `permissionMatrix` 的勾选需自行接后端保存；
- `profilePage` / `accountSettings` 为整页级组件，适合直接作为 `content`。


---

### `clients`

客户/客户端列表（apps-clients）。

> **类**：`zxf\XfAdmin\Components\Data\Clients`
> **文件**：`src/Components/Data/Clients.php`（108 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::clients([
    'title'       => '客户列表',
    'searchable'  => true,
    'type_filter' => ['全部', 'VIP', '潜在', '常规'],
    'clients'     => [
        [
            'name'     => 'Emily Parker',
            'email'    => 'emily@startupwave.io',
            'avatar'   => 'users/user-7.jpg',
            'phone'    => '+1 202-555-0147',
            'country'  => '美国',
            'enrolled' => '2026-03-12',
            'type'     => 'VIP',
            'job_title'=> '采购总监',
            'status'   => ['text' => '活跃', 'variant' => 'success'],
            'url'      => '#',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::clients([
    'title' => '客户列表',
    'searchable' => true,
    'type_filter' => [],
    'add_text' => '新增客户',
    'clients' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `clients[]` 元素键：`avatar`、`name`、`url`（默认 `#`）、`email`、`phone`、`country`、`enrolled`、`type`、`job_title`、`status`

> **渲染骨架**：主要 class `card-header` `border-light` `justify-content-between` `d-flex` `gap-2` `search-box` `card-body` `p-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'客户列表'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `searchable` | bool | `true` | 是否参与搜索；开关：非空 / 真值时启用对应区块 |
| `type_filter` | array | `[]` | 类型筛选条件 |
| `add_text` | string | `'新增客户'` | 「添加」按钮文案；**文本槽位**：输出前自动 HTML 转义 |
| `clients` | array | `[]` | 客户端 / 客户列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `companies`

公司列表（apps-companies）。

> **类**：`zxf\XfAdmin\Components\Data\Companies`
> **文件**：`src/Components/Data/Companies.php`（91 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::companies([
    'cols'      => 3,                     // 每行列数（1/2/3/4）
    'companies' => [
        [
            'name'      => '亚马逊',
            'logo'      => 'sellers/1.png',   // 相对包内 images/ 路径或完整 URL
            'website'   => 'www.amazon.com',
            'url'       => '#',
            'badges'    => [
                ['icon' => 'ti ti-map-pin', 'text' => '西雅图', 'color' => 'primary'],
                ['icon' => 'ti ti-shopping-cart', 'text' => '电商', 'color' => 'success'],
            ],
            'desc'      => '全球领先的电子商务与云计算公司。',
            'employees' => '150 万+',
            'revenue'   => '$514B',
            'rating'    => 4,                 // 0~5 星
            'followed'  => false,
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::companies([
    'cols' => 3,
    'companies' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `companies[]` 元素键：`logo`、`followed`、`url`（默认 `#`）、`name`、`website`、`badges`、`desc`、`employees`、`revenue`、`rating`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `cols` | int | `3` | 列数（栅格 / 分区列数） |
| `companies` | array | `[]` | 公司数据数组 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `companyCard`

公司卡（logo/简介/链接）。

> **类**：`zxf\XfAdmin\Components\Data\CompanyCard`
> **文件**：`src/Components/Data/CompanyCard.php`（92 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::companyCard([
    'cols'      => 2,
    'companies' => [
        [
            'logo'      => 'logos/amazon.svg',       // 相对 images/ 路径
            'name'      => '云杉科技',
            'website'   => 'www.example.com',
            'tags'      => [
                ['text' => '上海', 'icon' => 'ti ti-map-pin', 'variant' => 'primary'],
                ['text' => '电商', 'icon' => 'ti ti-shopping-cart', 'variant' => 'success'],
            ],
            'desc'      => '专注于企业级 SaaS 与云基础设施。',
            'stats'     => ['员工' => '1200+', '年营收' => '¥5.1亿'],
            'rating'    => 4,                        // 0-5 星
            'follow'    => true,                     // 是否显示关注按钮
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::companyCard([
    'companies' => [],
    'cols' => 2,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `companies[]` 元素键：`logo`、`name`、`website`、`follow`、`tags`、`desc`、`stats`、`rating`

> **渲染骨架**：主要 class `card` `d-flex` `flex-row` `p-3` `h-100` `avatar` `avatar-lg` `me-3`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `companies` | array | `[]` | 公司数据数组 |
| `cols` | int | `2` | 列数（栅格 / 分区列数） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `contactCard`

联系人卡（头像/电话/邮件）。

> **类**：`zxf\XfAdmin\Components\Data\ContactCard`
> **文件**：`src/Components/Data/ContactCard.php`（78 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::contactCard([
    'cols'     => 3,
    'contacts' => [
        [
            'avatar'   => 'users/user-5.jpg',
            'name'     => '苏菲',
            'role'     => '首席 UI/UX 设计师',
            'tag'      => '管理员',                 // 身份徽标（可空）
            'rating'   => 4.8,                      // 头像角标评分（可空）
            'email'    => 'sophia@example.com',
            'phone'    => '138-0000-0000',
            'location' => '上海',
            'url'      => '#',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::contactCard([
    'contacts' => [],
    'cols' => 3,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `contacts[]` 元素键：`avatar`、`name`、`rating`、`url`（默认 `#`）、`role`、`tag`

> **渲染骨架**：主要 class `card` `card-h-100` `card-body` `d-flex` `align-items-center` `mb-3` `me-3` `position-relative`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `contacts` | array | `[]` | 联系人列表 |
| `cols` | int | `3` | 列数（栅格 / 分区列数） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `contactList`

联系人列表（通讯录）。

> **类**：`zxf\XfAdmin\Components\Data\ContactList`
> **文件**：`src/Components/Data/ContactList.php`（53 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::contactList([
    'contacts' => [
        ['avatar' => 'users/user-1.jpg', 'name' => '王伟', 'role' => '产品经理', 'online' => true],
        ['avatar' => 'users/user-2.jpg', 'name' => '赵敏', 'role' => '设计师', 'online' => false],
    ],
    'title' => '团队成员',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::contactList([
    'contacts' => [],
    'title' => '团队成员',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `contacts[]` 元素键：`avatar`、`name`、`role`、`online`

> **渲染骨架**：主要 class `card` `card-header` `card-body` `p-0` `list-group` `list-group-flush` `position-relative` `flex-fill`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `contacts` | array | `[]` | 联系人列表 |
| `title` | string | `'团队成员'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `roles`

角色管理（角色列表+成员）。

> **类**：`zxf\XfAdmin\Components\Data\Roles`
> **文件**：`src/Components/Data/Roles.php`（49 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::roles([
    'title' => '角色与权限',
    'roles' => [
        ['name'=>'超级管理员','description'=>'拥有全部权限','users_count'=>1,'permissions_count'=>48,'color'=>'danger','guard'=>'admin'],
        ['name'=>'编辑','description'=>'内容管理','users_count'=>8,'permissions_count'=>20,'color'=>'info','guard'=>'web'],
    ],
    'permissions' => [ ... ],   // 可选：传入则渲染权限矩阵（PermissionMatrix）
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::roles([
    'title' => '',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'roles' => [],    // 角色列表（`id`+`name` 或键值形式）
    'permissions' => [],    // 权限列表
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `roles[]` 元素键：`color`（默认 `primary`）、`name`、`guard`（默认 `web`）、`description`、`users_count`、`permissions_count`

> **渲染骨架**：主要 class `d-flex` `justify-content-between` `align-items-center` `mb-3` `row` `g-3` `col-lg-4` `col-md-6`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `roles` | array | `[]` | 角色列表（`id`+`name` 或键值形式） |
| `permissions` | array | `[]` | 权限列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `permissionMatrix`

权限矩阵（角色×权限勾选表）。

> **类**：`zxf\XfAdmin\Components\Data\PermissionMatrix`
> **文件**：`src/Components/Data/PermissionMatrix.php`（53 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::permissionMatrix([
    'roles' => ['admin' => '管理员', 'editor' => '编辑'],
    'groups'=> [
        '用户管理' => [
            'user.view' => '查看', 'user.edit' => '编辑', 'user.delete' => '删除',
        ],
    ],
    'values' => ['admin' => ['user.view','user.edit','user.delete'], 'editor' => ['user.view','user.edit']],
    'readOnly'=> false,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::permissionMatrix([
    'roles' => [],
    'groups' => [],
    'values' => [],
    'readOnly' => false,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `roles[]`：`id`、`name`（角色名），也可直接传 `['admin' => '管理员']` 键值形式
- `groups[]`：`name`（权限组名）、`permissions`（该组权限数组，元素为 `id`+`name` 或键=>文案）
- `values[]`：已勾选矩阵：`[角色id => [权限id => true]]`，用于回填

> **渲染骨架**：主要 class `table` `table-bordered` `align-middle` `mb-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `roles` | array | `[]` | 角色列表（`id`+`name` 或键值形式）；源码用法：`$roles = (array) $this->get('roles');` |
| `groups` | array | `[]` | 分组数据（下拉分组 / 权限分组 / 设置分组）；源码用法：`$groups = (array) $this->get('groups');` |
| `values` | array | `[]` | 数值集合（图表/表单默认值/矩阵勾选值）；源码用法：`$values = (array) $this->get('values');` |
| `readOnly` | bool | `false` | 源码用法：`$ro = $this->get('readOnly') ? 'disabled' : '';` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `teamMember`

团队成员卡（头像/职位/操作）。

> **类**：`zxf\XfAdmin\Components\Data\TeamMember`
> **文件**：`src/Components/Data/TeamMember.php`（63 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::teamMember([
    'members' => [
        ['avatar' => 'users/avatar-1.jpg', 'name' => '张三', 'role' => '产品经理',
         'bio' => '负责产品规划与迭代',
         'social' => ['ti ti-twitter' => '#', 'ti ti-facebook' => '#', 'ti ti-linkedin' => '#']],
    ],
    'cols' => 4,            // 每行列数（1-6）
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::teamMember([
    'members' => [],
    'cols' => 4,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `members[]` 元素键：`avatar`、`name`、`role`、`bio`、`social`

> **渲染骨架**：主要 class `card` `card-hover` `h-100` `text-center` `card-body` `p-4` `avatar` `avatar-xxl`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `members` | array | `[]` | 成员列表 |
| `cols` | int | `4` | 列数（栅格 / 分区列数） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `projects`

项目列表（卡片/列表视图）。

> **类**：`zxf\XfAdmin\Components\Data\Projects`
> **文件**：`src/Components/Data/Projects.php`（67 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::projects([
    'title' => '项目管理',
    'projects' => [
        [
            'name' => '官网改版', 'client' => 'XX 集团', 'description' => '…',
            'progress' => 75, 'status' => 'active', // active|pending|completed|onhold
            'deadline' => '2026-09-30', 'budget' => '¥120k', 'spent' => '¥90k',
            'tasks_done' => 20, 'tasks_total' => 30,
            'members' => ['users/user-1.jpg','users/user-2.jpg'],
            'color' => 'primary',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::projects([
    'title' => '',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'projects' => [],    // 项目列表
    'view' => 'grid',    // 详情视图配置（viewRow 引擎，见数据表格文档）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `projects[]` 元素键：`status`（默认 `active`）、`color`（默认 `primary`）、`progress`、`name`、`client`、`description`、`tasks_done`、`tasks_total`、`budget`（默认 `-`）、`deadline`（默认 `-`）、`members`

> **渲染骨架**：主要 class `d-flex` `justify-content-between` `align-items-center` `mb-3` `row` `g-3` `col-xl-4` `col-md-6`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `projects` | array | `[]` | 项目列表 |
| `view` | string | `'grid'` | 详情视图配置（viewRow 引擎，见数据表格文档） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `projectDetails`

项目详情（概览/进度/成员）。

> **类**：`zxf\XfAdmin\Components\Data\ProjectDetails`
> **文件**：`src/Components/Data/ProjectDetails.php`（113 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::projectDetails([
    'project' => [
        'name' => '官网改版', 'client' => 'XX 集团', 'description' => '…',
        'progress' => 75, 'deadline' => '2026-09-30', 'budget' => '¥120k', 'spent' => '¥90k',
        'members' => [['name'=>'张三','avatar'=>'users/user-1.jpg','role'=>'设计']],
        'tasks' => [['title'=>'首页设计','done'=>true],['title'=>'前端开发','done'=>false]],
        'activity' => [['user'=>'李四','avatar'=>'users/user-2.jpg','text'=>'更新了设计稿','time'=>'2 小时前']],
        'files' => [['name'=>'需求文档.pdf','size'=>'1.2MB']],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::projectDetails([
    'project' => [],    // 项目数据
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `project[]` 元素键：`progress`、`name`、`client`（默认 `-`）、`description`、`tasks`、`activity`、`deadline`（默认 `-`）、`budget`（默认 `-`）、`spent`（默认 `-`）、`members`、`files`

> **渲染骨架**：主要 class `row` `g-3` `col-lg-8` `card` `mb-3` `card-body` `d-flex` `justify-content-between`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `project` | array | `[]` | 项目数据 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `projectActivity`

项目活动流（动态时间线）。

> **类**：`zxf\XfAdmin\Components\Data\ProjectActivity`
> **文件**：`src/Components/Data/ProjectActivity.php`（61 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::projectActivity([
    'title' => '项目动态',
    'items' => [
        ['user' => '张三', 'avatar' => 'users/user-1.jpg', 'title' => '创建了任务「登录页重构」', 'desc' => '从设计稿拆分为 5 个子任务', 'time' => '10 分钟前', 'color' => 'primary'],
        ['user' => '李四', 'avatar' => 'users/user-2.jpg', 'title' => '提交了代码', 'time' => '1 小时前', 'color' => 'success'],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::projectActivity([
    'title' => '项目动态',    // 标题文本（部分组件为弹窗/tooltip 标题）
    'items' => [],    // 条目数组（结构见各组件说明）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`avatar`、`color`（默认 `primary`）、`icon`（默认 `ti ti-activity`）、`user`、`time`、`title`、`desc`

> **渲染骨架**：主要 class `card` `card-body` `timeline` `timeline-item` `d-flex` `align-items-stretch` `timeline-dot` `flex-shrink-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'项目动态'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `projectTeamBoard`

项目团队看板（成员任务分配）。

> **类**：`zxf\XfAdmin\Components\Data\ProjectTeamBoard`
> **文件**：`src/Components/Data/ProjectTeamBoard.php`（95 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::projectTeamBoard([
    'title' => '项目团队',
    'cols'  => 3,
    'teams' => [
        [
            'code'     => 'IT-01',
            'name'     => 'Design Team',
            'badge'    => ['text' => 'New', 'variant' => 'primary'],
            'members'  => ['users/user-7.jpg', 'users/user-8.jpg', 'users/user-9.jpg', 'users/user-10.jpg'],
            'about'    => '负责 UI/UX 设计与品牌一致性。',
            'projects' => 25,
            'ranking'  => '#5',
            'budgets'  => '$20.3M',
            'progress' => 65,
            'updated'  => '1 hour ago',
            'url'      => '#',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::projectTeamBoard([
    'title' => '项目团队',
    'cols' => 3,
    'teams' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `teams[]` 元素键：`code`、`name`、`badge`、`members`、`about`、`projects`（默认 `0`）、`ranking`（默认 `-`）、`budgets`（默认 `-`）、`progress`、`updated`、`url`（默认 `#`）

> **渲染骨架**：主要 class `d-flex` `align-items-sm-center` `flex-sm-row` `flex-column` `mb-3` `row` `card` `card-h-100`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'项目团队'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `cols` | int | `3` | 列数（栅格 / 分区列数） |
| `teams` | array | `[]` | 团队列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `taskList`

任务清单（勾选/优先级）。

> **类**：`zxf\XfAdmin\Components\Data\TaskList`
> **文件**：`src/Components/Data/TaskList.php`（90 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::taskList([
    'title'  => '我的任务',
    'tasks'  => [
        [
            'id'        => 1,
            'title'     => '完成季度报表',
            'done'      => false,
            'priority'  => 'high',        // high|medium|low
            'assignee'  => '张三',
            'avatar'    => 'users/user-1.jpg',
            'due'       => '2026-08-20',
            'tag'       => '报表',
        ],
    ],
    'filterable' => true,
    'addable'    => true,
    'add_url'    => '#',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::taskList([
    'title' => '',
    'tasks' => [],
    'filterable' => true,
    'addable' => true,
    'add_url' => 'javascript:void(0);',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `tasks[]` 元素键：`id`、`done`、`priority`（默认 `medium`）、`avatar`、`assignee`、`due`、`tag`、`title`

> **渲染骨架**：主要 class `card-header` `d-flex` `align-items-center` `justify-content-between` `gap-2` `flex-wrap` `btn-group` `btn-group-sm`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `tasks` | array | `[]` | 源码用法：`$tasks = (array) $this->get('tasks');` |
| `filterable` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `addable` | bool | `true` | 开关：非空 / 真值时启用对应区块 |
| `add_url` | string | `'javascript:void(0);'` | URL：经协议白名单校验（拦截 `javascript:` 等） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `todoList`

待办列表（添加/完成）。

> **类**：`zxf\XfAdmin\Components\Data\TodoList`
> **文件**：`src/Components/Data/TodoList.php`（55 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::todoList([
    'title'  => '今日任务',
    'items'  => [
        ['text' => '回顾周报', 'done' => true,  'priority' => 'high'],
        ['text' => '发布版本', 'done' => false, 'priority' => 'medium'],
    ],
    'addable' => true,        // 允许新增
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::todoList([
    'title' => '',
    'items' => [],
    'addable' => false,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`text`、`done`、`priority`

> **渲染骨架**：主要 class `card-header` `bg-transparent` `border-bottom` `d-flex` `align-items-center` `badge` `bg-primary-subtle` `text-primary`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `addable` | bool | `false` | 开关：非空 / 真值时启用对应区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `issueTracker`

问题追踪（看板式缺陷管理）。

> **类**：`zxf\XfAdmin\Components\Data\IssueTracker`
> **文件**：`src/Components/Data/IssueTracker.php`（112 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::issueTracker([
    'title'      => '问题列表',
    'searchable' => true,               // 是否显示前端过滤搜索框
    'add_text'   => '新建问题',          // 空字符串则不显示按钮
    'issues'     => [
        [
            'id'       => 'ISSUE-104',
            'title'    => '移动端用户资料无法保存',
            'status'   => '进行中',                      // 状态文字
            'variant'  => 'warning',                     // 状态色 primary/success/...
            'assignee' => ['avatar' => 'users/user-3.jpg', 'name' => '李雷'],
            'created'  => '2026-02-10',
            'due'      => '2026-02-18',
            'labels'   => ['Bug', 'Mobile'],
            'progress' => 60,                            // 0-100
            'comments' => 8,
            'url'      => '#',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::issueTracker([
    'title' => '',
    'searchable' => true,
    'add_text' => '',
    'issues' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `issues[]` 元素键：`variant`（默认 `secondary`）、`status`、`url`（默认 `#`）、`id`、`title`、`assignee`、`created`、`due`、`labels`、`progress`、`comments`

> **渲染骨架**：主要 class `card-header` `d-flex` `flex-wrap` `align-items-center` `justify-content-between` `gap-2` `border-light` `flex-grow-1`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `searchable` | bool | `true` | 是否参与搜索 |
| `add_text` | string | `''` | 「添加」按钮文案 |
| `issues` | array | `[]` | 问题 / 工单列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `kanban`

看板（拖拽列/卡片，项目与工单管理）。

> **类**：`zxf\XfAdmin\Components\Data\Kanban`
> **文件**：`src/Components/Data/Kanban.php`（164 行）
> **依赖插件**：`sortablejs`、`simplebar`

**用法示例**

```php
XfAdmin::kanban([
    'columns' => [
        ['id'=>'todo','title'=>'待办','variant'=>'danger',
         'cards'=>[
            ['title'=>'设计首页','label'=>'设计','variant'=>'info','text'=>'…','members'=>['users/user-1.jpg'],
             'due'=>'今天','progress'=>60,'comments'=>3,'attachments'=>1],
         ]],
    ],
    'search' => true,            // 顶栏搜索框
    'addText' => '新建卡片',      // 列头 / 顶栏新增按钮文案
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::kanban([
    'columns' => [],
    'search' => true,
    'addText' => '新建卡片',
    'class' => '',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `columns[]` 元素键：`id`（默认 `col`）、`cards`、`variant`（默认 `primary`）、`title`

> **渲染骨架**：主要 class `card-header` `border-light` `align-items-center` `gap-2` `app-search` `flex-grow-1` `app-search-icon` `ms-auto`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `columns` | array | `[]` | 列定义数组 |
| `search` | bool | `true` | 是否启用搜索；源码用法：`$search = $this->get('search');` |
| `addText` | string | `'新建卡片'` | 源码用法：`$addText = $this->get('addText');` |
| `class` | string | `''` | 附加到根元素的自定义 class；**文本槽位**：输出前自动 HTML 转义 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `deals`

交易/商机列表（CRM 看板式）。

> **类**：`zxf\XfAdmin\Components\Data\Deals`
> **文件**：`src/Components/Data/Deals.php`（91 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::deals([
    'stages' => [
        'lead'     => ['name' => '线索', 'color' => 'info'],
        'contact'  => ['name' => '接洽', 'color' => 'primary'],
        'proposal' => ['name' => '方案', 'color' => 'warning'],
        'won'      => ['name' => '成交', 'color' => 'success'],
    ],
    'deals' => [
        ['id'=>1,'stage'=>'lead','title'=>'XX 采购','customer'=>'A 公司','amount'=>120000,'owner'=>'张三','prob'=>40],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::deals([
    // stages
    'stages' => [
        'lead' => [/* … */],
        'contact' => [/* … */],
        'proposal' => [/* … */],
        'won' => [/* … */],
    ],
    'deals' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `deals[]` 元素键：`stage`

> **渲染骨架**：主要 class `xf-deals` `row` `g-3` `text-muted` `text-center` `small` `py-3` `border`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `stages` | array | `['lead'=>{…}, 'contact'=>{…}, 'proposal'=>{…}, 'won'=>{…}]` | 数组结构（见「全参数示例」）；源码用法：`$stages = (array) $this->get('stages');` |
| `deals` | array | `[]` | 源码用法：`$deals = (array) $this->get('deals');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `userProfile`

用户资料卡（详情展示）。

> **类**：`zxf\XfAdmin\Components\Data\UserProfile`
> **文件**：`src/Components/Data/UserProfile.php`（80 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::userProfile([
    'avatar'   => 'users/user-1.jpg',
    'name'     => '陈一鸣',
    'title'    => '高级前端工程师',
    'bio'      => '热爱开源，专注中后台体验优化。',
    'stats'    => [['label' => '项目', 'value' => 24], ['label' => '关注', 'value' => 1.2 . 'k'], ['label' => '粉丝', 'value' => 3.4 . 'k']],
    'actions'  => ['message' => true, 'follow' => true],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::userProfile([
    'avatar' => '',
    'cover' => '',
    'name' => '匿名用户',
    'title' => '',
    'bio' => '',
    'stats' => [],
    // actions
    'actions' => [
        'message' => true,
        'follow' => true,
    ],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `stats[]` 元素键：`value`、`label`
- `actions[]` 元素键：`message`、`follow`

> **渲染骨架**：主要 class `card` `overflow-hidden` `profile-cover` `card-body` `text-center` `rounded-circle` `avatar-lg` `shadow`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `avatar` | string | `''` | 头像地址（自动解析为包内图片 URL） |
| `cover` | string | `''` | 封面图地址 |
| `name` | string | `'匿名用户'` | 表单字段名 / 语义名称 |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `bio` | string | `''` | 个人简介 |
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `actions` | array | `['message'=>true, 'follow'=>true]` | 操作区内容（按钮组 / 行操作定义） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `profileHeader`

个人资料头部（封面+头像+统计）。

> **类**：`zxf\XfAdmin\Components\Data\ProfileHeader`
> **文件**：`src/Components/Data/ProfileHeader.php`（86 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::profileHeader([
    'cover'   => 'stock/small-10.jpg',
    'avatar'  => 'users/avatar-1.jpg',
    'name'    => '张三',
    'role'    => '前端工程师',
    'location'=> '深圳',
    'stats'   => [
        ['label' => '关注', 'value' => '2.5k'],
        ['label' => '粉丝', 'value' => '13k'],
    ],
    'actions' => '按钮HTML',
    'tabs'    => [['label' => '概览', 'active' => true, 'href' => '#o'], ...],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::profileHeader([
    'cover' => null,
    'avatar' => 'users/avatar-1.jpg',
    'name' => '',
    'role' => null,
    'location' => null,
    'stats' => [],
    'actions' => null,
    'tabs' => [],
    'verified' => false,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `stats[]` 元素键：`value`、`label`
- `tabs[]` 元素键：`active`、`href`（默认 `#`）、`toggle`、`label`

> **渲染骨架**：主要 class `profile-cover` `bg-primary` `card-body` `d-flex` `flex-wrap` `align-items-center` `gap-3` `avatar`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `cover` | mixed | `null` | 封面图地址；开关：非空 / 真值时启用对应区块 |
| `avatar` | string | `'users/avatar-1.jpg'` | 头像地址（自动解析为包内图片 URL）；源码用法：`$html .= '<span class="avatar avatar-xxl flex-shrink-0"><img src="' . $this->e(\zxf\X…` |
| `name` | string | `''` | 表单字段名 / 语义名称；**文本槽位**：输出前自动 HTML 转义 |
| `role` | mixed | `null` | 角色；源码用法：`$meta = array_filter([$this->get('role'), $this->get('location')]);` |
| `location` | mixed | `null` | 源码用法：`$meta = array_filter([$this->get('role'), $this->get('location')]);` |
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `actions` | mixed | `null` | 操作区内容（按钮组 / 行操作定义）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块 |
| `tabs` | array | `[]` | 选项卡数组 |
| `verified` | bool | `false` | 开关：非空 / 真值时启用对应区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `profilePage`

个人资料页（头部+标签页+动态）。

> **类**：`zxf\XfAdmin\Components\Data\ProfilePage`
> **文件**：`src/Components/Data/ProfilePage.php`（103 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::profilePage([
    'cover'    => 'stock/small-1.jpg',                       // 或 'gradient:linear-gradient(...)'
    'avatar'   => 'users/user-1.jpg',
    'name'     => '张伟',
    'verified' => true,
    'role'     => '高级产品经理',
    'meta'     => [['icon' => 'ti ti-map-pin', 'text' => '深圳']],
    'stats'    => [['value' => '128', 'label' => '项目']],
    'actions'  => [['text' => '关注', 'class' => 'btn-primary', 'icon' => 'ti ti-user-plus']],
    'tabs'     => [['title' => '动态', 'content' => ..., 'active' => true]],  // XfAdmin::tabs 的 items
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::profilePage([
    'cover' => '',
    'avatar' => '',
    'name' => '',
    'verified' => false,
    'role' => '',
    'meta' => [],
    'stats' => [],
    'actions' => [],
    'tabs' => [],
    'content' => null,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `meta[]` 元素键：`icon`、`text`
- `stats[]` 元素键：`value`、`label`
- `actions[]` 元素键：`url`（默认 `#`）、`icon`、`text`

> **渲染骨架**：主要 class `card` `border-0` `shadow-sm` `overflow-hidden` `mb-3` `profile-cover` `card-body` `position-relative`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `cover` | string | `''` | 封面图地址 |
| `avatar` | string | `''` | 头像地址（自动解析为包内图片 URL）；源码用法：`$avatar = (string) $this->get('avatar');` |
| `name` | string | `''` | 表单字段名 / 语义名称；**文本槽位**：输出前自动 HTML 转义 |
| `verified` | bool | `false` | 开关：非空 / 真值时启用对应区块 |
| `role` | string | `''` | 角色；**文本槽位**：输出前自动 HTML 转义 |
| `meta` | array | `[]` | 附加信息（时间 / 作者等） |
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `actions` | array | `[]` | 操作区内容（按钮组 / 行操作定义） |
| `tabs` | array | `[]` | 选项卡数组 |
| `content` | mixed | `null` | 内容区（可为 HTML 字符串、组件实例或数组）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `accountSettings`

账户设置整页（tabs+表单）。

> **类**：`zxf\XfAdmin\Components\Data\AccountSettings`
> **文件**：`src/Components/Data/AccountSettings.php`（230 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::accountSettings([
    'tabs' => [
        ['title' => 'General', 'icon' => 'ti-settings', 'content' => '', 'active' => true],
        ['title' => 'Change Password', 'icon' => 'ti-lock', 'content' => ''],
        ['title' => 'Email Notifications', 'icon' => 'ti-bell', 'content' => ''],
        ['title' => 'Export Data', 'icon' => 'ti-download', 'content' => ''],
    ],
    'user' => [
        'avatar' => 'avatars/1.png',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => 'Administrator',
        'bio' => '...',
        'timezone' => 'Asia/Shanghai',
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::accountSettings([
    'tabs' => [],
    'user' => [],
    'activeTab' => 0,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `tabs[]` 元素键：`icon`（默认 `ti-settings`）、`title`
- `user[]` 元素键：`avatar`、`name`、`role`、`email`、`bio`、`timezone`、`phone`、`company`

> **渲染骨架**：主要 class `row` `g-4` `col-lg-3` `card` `card-body` `text-center` `rounded-circle` `bg-primary`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `tabs` | array | `[]` | 选项卡数组 |
| `user` | array | `[]` | 用户信息（name/avatar/email/role 等） |
| `activeTab` | int | `0` | 默认激活的选项卡 id |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `voteList`

投票列表（选项+进度条）。

> **类**：`zxf\XfAdmin\Components\Data\VoteList`
> **文件**：`src/Components/Data/VoteList.php`（81 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::voteList([
    'title' => '社区投票',
    'items' => [
        [
            'votes'    => 35,
            'title'    => '远程办公是否应成为长期选项？',
            'desc'     => '本投票探讨远程办公是否应作为长期弹性选项保留。',
            'author'   => ['avatar' => 'users/user-7.jpg', 'name' => '陈晓'],
            'date'     => '2026-01-12',
            'tag'      => '职场',
            'comments' => 89,
            'ends'     => '5 天后',
            'total'    => 1284,
            'status'   => '进行中',       // 状态文字（可空）
            'variant'  => 'success',      // 状态色
            'url'      => '#',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::voteList([
    'title' => '',
    'items' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`votes`、`url`（默认 `#`）、`title`、`desc`、`author`、`date`、`tag`、`comments`、`ends`、`total`、`status`、`variant`（默认 `secondary`）

> **渲染骨架**：主要 class `card-header` `border-light` `card-body` `p-0` `border-bottom` `border-dashed` `px-4` `py-3`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `activityFeed`

活动动态流（时间线式操作记录）。

> **类**：`zxf\XfAdmin\Components\Data\ActivityFeed`
> **文件**：`src/Components/Data/ActivityFeed.php`（53 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::activityFeed([
    'items' => [
        ['avatar' => 'users/avatar-1.jpg', 'user' => '张三', 'action' => '评论了任务',
         'target' => '首页改版', 'time' => '2 小时前', 'text' => '看起来不错'],
        ['icon' => 'ti ti-check', 'variant' => 'success', 'user' => '系统',
         'action' => '完成部署', 'time' => '昨天'],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::activityFeed([
    'items' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`avatar`、`variant`（默认 `primary`）、`icon`（默认 `ti ti-point`）、`user`、`action`、`target`、`href`（默认 `#`）、`text`、`time`

> **渲染骨架**：主要 class `d-flex` `gap-3` `pb-3` `mb-3` `border-bottom` `avatar` `avatar-md` `flex-shrink-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

