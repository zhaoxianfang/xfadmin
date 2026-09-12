# 业务组件（一）电商 · 商品 · 订单 · 发票

> 商品、分类、购物车、结算、订单、退款、库存、销售、发票与电商仪表盘。

<!-- 本文件由 `php tools/gen_component_docs.php` 自动生成，请勿手工编辑组件参数表；
     如需修改请改源码注释后重新生成。章节说明可手工维护。 -->

## 目录

- [`productCard`](#productcard) — 商品卡（图/标题/价格/角标）
- [`productsGrid`](#productsgrid) — 商品网格（卡片墙）
- [`productCategories`](#productcategories) — 商品分类管理（树形分类）
- [`productAdd`](#productadd) — 商品添加表单
- [`productDetails`](#productdetails) — 商品详情（图集/规格/评价）
- [`productViews`](#productviews) — 商品浏览统计（热度图表）
- [`shoppingCart`](#shoppingcart) — 购物车（商品+数量+合计）
- [`cartSummary`](#cartsummary) — 购物车摘要（结算侧栏小计）
- [`checkout`](#checkout) — 结算页（地址/支付/提交）
- [`orders`](#orders) — 订单列表（电商订单管理）
- [`orderDetails`](#orderdetails) — 订单详情（商品/收货/金额）
- [`orderTrackingTimeline`](#ordertrackingtimeline) — 订单追踪时间线（物流状态流）
- [`purchasedOrders`](#purchasedorders) — 已购订单（买家视图）
- [`refunds`](#refunds) — 退款管理（退款单列表）
- [`sales`](#sales) — 销售数据（图表+列表）
- [`customers`](#customers) — 客户列表（电商客户管理）
- [`sellers`](#sellers) — 卖家列表（电商卖家管理）
- [`sellerDetails`](#sellerdetails) — 卖家详情（店铺/商品/评价）
- [`reviewList`](#reviewlist) — 评价列表（星级+内容+晒图）
- [`attributes`](#attributes) — 商品属性管理（规格键值）
- [`ecommerceSettings`](#ecommercesettings) — 电商设置（通用配置表单）
- [`ecommerceDashboard`](#ecommercedashboard) — 电商仪表盘（销售/订单/库存概览）
- [`marketplace`](#marketplace) — 应用市场（插件/模板网格）
- [`warehouse`](#warehouse) — 仓库管理（库存/出入库）
- [`invoiceCreate`](#invoicecreate) — 发票创建表单
- [`invoiceDetail`](#invoicedetail) — 发票详情整页
- [`invoiceList`](#invoicelist) — 发票列表（列表+状态+金额）
- [`invoiceTable`](#invoicetable) — 发票明细表格
- [`invoiceView`](#invoiceview) — 发票查看（打印友好视图）
- [`filterSidebar`](#filtersidebar) — 筛选侧栏（分类/价格/标签过滤）
- [`featureComparisonTable`](#featurecomparisontable) — 功能对比表（方案×特性矩阵）

## 本章导读

业务组件是**数据驱动的整块 UI**：传入业务数据数组即可渲染完整区块，
适合快速搭建电商/订单/发票等后台页面。

### 组合范式

```php
echo XfAdmin::row(['cols' => [
    ['width' => 8, 'content' => XfAdmin::orders(['orders' => $orders])],
    ['width' => 4, 'content' => XfAdmin::cartSummary(['items' => $cart, 'total' => $total])],
]]);

echo XfAdmin::productsGrid(['products' => \$products]);
echo XfAdmin::invoiceDetail(['invoice' => \$invoice, 'items' => \$items]);
```

### 约定与陷阱

- 业务组件多为**整页/整块**，通常作为 `content` 或 `card.body` 使用；
- 数据字段缺失时会安全降级（显示占位或跳过），不会报错；
- 图片字段统一走 `img()` 解析（外链 / data URI / 包内相对路径）；
- 需要对接真实后端时，优先把业务数据格式化为组件期望的数组结构。


---

### `productCard`

商品卡（图/标题/价格/角标）。

> **类**：`zxf\XfAdmin\Components\Data\ProductCard`
> **文件**：`src/Components/Data/ProductCard.php`（76 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::productCard([
    'image'    => 'products/1.png',
    'title'    => '男士运动鞋',
    'category' => '鞋类',
    'price'    => '¥299',
    'old_price'=> '¥399',
    'rating'   => 4.5,
    'rating_count' => 128,
    'badge'    => ['text' => '热销', 'variant' => 'danger'],
    'href'     => '#',
    'actions'  => 'HTML',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::productCard([
    'image' => null,
    'title' => '',
    'category' => null,
    'price' => null,
    'old_price' => null,
    'rating' => null,
    'rating_count' => null,
    'badge' => null,
    'href' => '#',
    'actions' => null,
]);
```

</details>

> **渲染骨架**：主要 class `position-relative` `xf-product-img` `bg-light` `d-flex` `align-items-center` `justify-content-center` `text-muted` `card-body`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `image` | mixed | `null` | 图片地址（支持外链 / data URI / 包内 images 相对路径）；源码用法：`$img = $this->get('image');` |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `category` | mixed | `null` | 分类；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `price` | mixed | `null` | 价格数值；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `old_price` | mixed | `null` | **文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `rating` | mixed | `null` | 开关：非空 / 真值时启用对应区块；为 `null` 时不渲染该区块 |
| `rating_count` | mixed | `null` | 源码用法：`'count' => $this->get('rating_count'),` |
| `badge` | mixed | `null` | 徽标文本或 `['text'=>..,'class'=>..]`；开关：非空 / 真值时启用对应区块 |
| `href` | string | `'#'` | 链接地址（同 url）；URL：经协议白名单校验（拦截 `javascript:` 等） |
| `actions` | mixed | `null` | 操作区内容（按钮组 / 行操作定义）；**内容槽位**：`raw()` 原样输出（可传 HTML / 组件 / 闭包 / 数组）；开关：非空 / 真值时启用对应区块 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `productsGrid`

商品网格（卡片墙）。

> **类**：`zxf\XfAdmin\Components\Data\ProductsGrid`
> **文件**：`src/Components/Data/ProductsGrid.php`（115 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::productsGrid([
    'products' => [
        ['title' => '商品名', 'image' => 'products/1.png', 'price' => 89.00, 'oldPrice' => 120.00, 'category' => '电子产品', 'stock' => 45, 'status' => 'active'],
        ...
    ],
    'currency' => '¥',
    'columns' => [4, 3, 2, 1], // xl, lg, md, sm
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::productsGrid([
    'products' => [],
    'currency' => '¥',
    // columns
    'columns' => [
        4,
        3,
        2,
        1,
    ],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `products[]` 元素键：`title`、`image`、`price`、`oldPrice`、`category`、`stock`、`status`（默认 `active`）

> **渲染骨架**：主要 class `d-flex` `justify-content-between` `align-items-center` `mb-3` `badge` `text-bg-secondary` `rounded-pill` `ms-2`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `products` | array | `[]` | 商品数据数组 |
| `currency` | string | `'¥'` | 货币符号 |
| `columns` | array | `[4, 3, 2, 1]` | 列定义数组 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `productCategories`

商品分类管理（树形分类）。

> **类**：`zxf\XfAdmin\Components\Data\ProductCategories`
> **文件**：`src/Components/Data/ProductCategories.php`（84 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::productCategories([
    'title'      => '商品分类',
    'categories' => [
        [
            'name'     => '家具家居',
            'image'    => 'products/1.png',    // 相对包内 images/ 路径或完整 URL
            'slug'     => 'furniture',
            'products' => 5248,
            'orders'   => '95.6k',
            'earnings' => '¥4,050 万',
            'modified' => '2026-04-18 12:24',
            'status'   => ['text' => '启用', 'variant' => 'success'],
            'url'      => '#',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::productCategories([
    'title' => '商品分类',
    'searchable' => true,
    'add_text' => '新增分类',
    'categories' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `categories[]` 元素键：`image`、`url`（默认 `#`）、`name`、`slug`、`products`、`orders`、`earnings`、`modified`、`status`

> **渲染骨架**：主要 class `card-header` `border-light` `justify-content-between` `d-flex` `gap-2` `search-box` `card-body` `p-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'商品分类'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `searchable` | bool | `true` | 是否参与搜索；开关：非空 / 真值时启用对应区块 |
| `add_text` | string | `'新增分类'` | 「添加」按钮文案；**文本槽位**：输出前自动 HTML 转义 |
| `categories` | array | `[]` | 分类列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `productAdd`

商品添加表单。

> **类**：`zxf\XfAdmin\Components\Data\ProductAdd`
> **文件**：`src/Components/Data/ProductAdd.php`（115 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::productAdd([
    'action'     => '/admin/products',
    'brands'     => ['Apple', 'Samsung'],
    'categories' => ['手机数码', '家用电器'],
    'tags'       => ['新品', '热卖'],
    'values'     => ['name' => 'iPhone 17', 'sku' => 'IP17-001', 'stock' => 100],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::productAdd([
    'action' => '#',
    'method' => 'POST',
    'brands' => [],
    'categories' => [],
    'sub_categories' => [],
    // statuses
    'statuses' => [
        '草稿',
        '上架',
        '下架',
    ],
    'tags' => [],
    'values' => [],
    'submit_text' => '保存商品',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `values[]` 元素键：`name`、`sku`、`stock`、`description`、`price`、`discount_value`、`brand`、`category`、`sub_category`、`status`

> **渲染骨架**：主要 class `row` `g-3` `col-xl-9` `card` `card-header` `card-body` `col-md-6` `text-danger`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `action` | string | `'#'` | 表单提交地址 / 动作类型 |
| `method` | string | `'POST'` | HTTP 方法（GET/POST/PUT/DELETE） |
| `brands` | array | `[]` | 品牌列表 |
| `categories` | array | `[]` | 分类列表 |
| `sub_categories` | array | `[]` | 子分类列表 |
| `statuses` | array | `['草稿', '上架', '下架']` | 数组结构（见「全参数示例」） |
| `tags` | array | `[]` | 标签数组 / 是否启用标签输入 |
| `values` | array | `[]` | 数值集合（图表/表单默认值/矩阵勾选值） |
| `submit_text` | string | `'保存商品'` | **文本槽位**：输出前自动 HTML 转义 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `productDetails`

商品详情（图集/规格/评价）。

> **类**：`zxf\XfAdmin\Components\Data\ProductDetails`
> **文件**：`src/Components/Data/ProductDetails.php`（165 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::productDetails([
    'product' => [
        'name' => '无线降噪耳机', 'sku' => 'SKU-001', 'price' => '¥899', 'old_price' => '¥1099',
        'rating' => 4.5, 'reviews' => 128, 'stock' => '有货',
        'images' => ['products/1.png','products/2.png'],
        'description' => '...', 'features' => ['主动降噪','40h 续航'],
        'variants' => ['color' => ['黑','白','蓝'], 'size' => ['S','M','L']],
        'category' => '数码', 'brand' => 'Acme',
        'tabs' => [['title'=>'规格','body'=>'...'],['title'=>'评价','body'=>'...']],
        'related' => [ ['title'=>..,'price'=>..,'image'=>..], ... ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::productDetails([
    'product' => [],    // 商品数据
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `product[]` 元素键：`images`、`image`、`category`、`name`、`rating`、`reviews`、`price`、`old_price`、`stock`、`description`、`variants`、`features`、`tabs`、`related`

> **渲染骨架**：主要 class `row` `g-4` `col-lg-5` `border` `rounded` `overflow-hidden` `mb-2` `d-flex`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `product` | array | `[]` | 商品数据 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `productViews`

商品浏览统计（热度图表）。

> **类**：`zxf\XfAdmin\Components\Data\ProductViews`
> **文件**：`src/Components/Data/ProductViews.php`（71 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::productViews([
    'products' => [
        ['name' => '商品A', 'image' => 'products/1.png', 'views' => 12560, 'uniqueViews' => 8450, 'avgTime' => '3:24', 'ctr' => 4.8, 'sales' => 230],
        ...
    ],
    'totalViews' => '128,430',
    'totalUnique' => '45,210',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::productViews([
    'products' => [],
    'totalViews' => '0',
    'totalUnique' => '0',
    'title' => '商品浏览量',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `products[]` 元素键：`name`、`views`、`uniqueViews`、`avgTime`（默认 `0:00`）、`ctr`、`sales`

> **渲染骨架**：主要 class `row` `mb-4` `col-md-4` `card` `text-bg-primary` `card-body` `text-center` `text-white-50`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `products` | array | `[]` | 商品数据数组 |
| `totalViews` | string | `'0'` | 浏览总数 |
| `totalUnique` | string | `'0'` | 独立访客总数 |
| `title` | string | `'商品浏览量'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `shoppingCart`

购物车（商品+数量+合计）。

> **类**：`zxf\XfAdmin\Components\Data\ShoppingCart`
> **文件**：`src/Components/Data/ShoppingCart.php`（124 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::shoppingCart([
    'items' => [
        ['image' => 'products/p1.png', 'name' => '商品名', 'sku' => 'SKU-001', 'price' => 899, 'qty' => 2, 'subtotal' => 1798, 'color' => '黑色', 'size' => 'M'],
        // ...
    ],
    'subtotal' => 1798,
    'shipping' => 0,
    'tax' => 233.74,
    'total' => 2031.74,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::shoppingCart([
    'items' => [],
    'subtotal' => 0,
    'shipping' => 0,
    'tax' => 0,
    'discount' => 0,
    'total' => 0,
    'currency' => '¥',
    'emptyMessage' => '购物车为空',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`image`、`name`、`sku`、`price`、`qty`、`subtotal`、`color`、`size`

> **渲染骨架**：主要 class `text-center` `py-5` `mb-3` `btn` `btn-primary` `mt-2` `row` `g-4`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `subtotal` | int | `0` | 小计金额 |
| `shipping` | int | `0` | 运费 |
| `tax` | int | `0` | 税费 |
| `discount` | int | `0` | 折扣（金额或百分比） |
| `total` | int | `0` | 合计数值 |
| `currency` | string | `'¥'` | 货币符号 |
| `emptyMessage` | string | `'购物车为空'` | **文本槽位**：输出前自动 HTML 转义 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `cartSummary`

购物车摘要（结算侧栏小计）。

> **类**：`zxf\XfAdmin\Components\Data\CartSummary`
> **文件**：`src/Components/Data/CartSummary.php`（59 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::cartSummary([
    'subtotal'  => 1280.00,
    'shipping'  => 20.00,
    'discount'  => 100.00,
    'currency'  => '￥',
    'promo'     => true,         // 是否显示优惠码输入
    'button'    => ['text' => '去结算', 'variant' => 'primary'],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::cartSummary([
    'subtotal' => 0,
    'shipping' => 0,
    'discount' => 0,
    'currency' => '￥',
    'promo' => true,
    // button
    'button' => [
        'text' => '去结算',
        'variant' => 'primary',
    ],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `button[]` 元素键：`variant`（默认 `primary`）、`text`（默认 `去结算`）

> **渲染骨架**：主要 class `card` `border-0` `shadow-sm` `card-header` `bg-transparent` `border-bottom` `fw-semibold` `card-body`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `subtotal` | int | `0` | 小计金额；源码用法：`$sub = (float) $this->get('subtotal');` |
| `shipping` | int | `0` | 运费；源码用法：`$ship = (float) $this->get('shipping');` |
| `discount` | int | `0` | 折扣（金额或百分比）；源码用法：`$disc = (float) $this->get('discount');` |
| `currency` | string | `'￥'` | 货币符号；源码用法：`$cur = $this->get('currency');` |
| `promo` | bool | `true` | 源码用法：`$promo = $this->get('promo');` |
| `button` | array | `['text'=>'去结算', 'variant'=>'primary']` | 数组结构（见「全参数示例」） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `checkout`

结算页（地址/支付/提交）。

> **类**：`zxf\XfAdmin\Components\Data\Checkout`
> **文件**：`src/Components/Data/Checkout.php`（142 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::checkout([
    'steps' => [
        ['label' => 'Billing info', 'icon' => 'ti-home', 'done' => true],
        ['label' => 'Shipping info', 'icon' => 'ti-truck-delivery', 'active' => true],
        ['label' => 'Payment info', 'icon' => 'ti-credit-card'],
        ['label' => 'Finish', 'icon' => 'ti-check'],
    ],
    'orderSummary' => [
        'subtotal' => 1798.00,
        'shipping' => 29.00,
        'tax' => 233.74,
        'total' => 2060.74,
    ],
    'currency' => '$',
    'currentStep' => 1, // zero-indexed
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::checkout([
    'steps' => [],
    // orderSummary
    'orderSummary' => [
        'subtotal' => 0,
        'shipping' => 0,
        'tax' => 0,
        'total' => 0,
    ],
    'currency' => '¥',
    'currentStep' => 0,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `steps[]` 元素键：`icon`（默认 `ti-circle`）、`label`、`done`
- `orderSummary[]` 元素键：`subtotal`、`shipping`、`tax`、`discount`、`total`、`items`

> **渲染骨架**：主要 class `row` `g-4` `col-lg-8` `card` `card-body` `py-3` `xf-checkout-steps` `d-flex`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `steps` | array | `[]` | 步骤数组（向导 / 步骤条） |
| `orderSummary` | array | `['subtotal'=>0, 'shipping'=>0, 'tax'=>0, 'total'=>0]` | 数组结构（见「全参数示例」） |
| `currency` | string | `'¥'` | 货币符号 |
| `currentStep` | int | `0` | 当前步骤索引（向导 / 步骤条） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `orders`

订单列表（电商订单管理）。

> **类**：`zxf\XfAdmin\Components\Data\Orders`
> **文件**：`src/Components/Data/Orders.php`（160 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::orders([
    'title'  => '订单管理',
    'orders' => [
        [
            'id' => '#ORD-1001', 'customer' => '张三', 'avatar' => 'users/user-1.jpg',
            'email' => 'z@x.com', 'date' => '2026-07-20', 'time' => '10:10',
            'items' => 3, 'total' => '¥320.00',
            'status' => 'completed',        // pending|processing|completed|refunded|cancelled
            'paid'   => true,               // 付款状态（true=已付款 / false=未付款 / 'refund'=退款中）
            'payment' => '支付宝',
        ],
    ],
    'searchable' => true,   // 头部搜索框
    'filterable' => true,   // 状态/付款筛选下拉
    'selectable' => true,   // 勾选框 + 全选 + 批量删除
    'page_size'  => 10,     // 前端分页每页条数（0 = 不分页）
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::orders([
    'title' => '',
    'orders' => [],
    'searchable' => true,
    'filterable' => true,
    'selectable' => true,
    'page_size' => 10,
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `orders[]` 元素键：`status`（默认 `pending`）、`paid`、`url`（默认 `#`）、`id`、`date`、`time`、`avatar`、`customer`、`email`、`total`、`payment`（默认 `-`）

> **渲染骨架**：主要 class `card-header` `border-light` `d-flex` `flex-wrap` `align-items-center` `gap-2` `app-search` `xf-card-search`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `orders` | array | `[]` |  |
| `searchable` | bool | `true` | 是否参与搜索；源码用法：`$searchable = (bool) $this->get('searchable');` |
| `filterable` | bool | `true` | 源码用法：`$filterable = (bool) $this->get('filterable');` |
| `selectable` | bool | `true` | 源码用法：`$selectable = (bool) $this->get('selectable');` |
| `page_size` | int | `10` | 源码用法：`$pageSize = (int) $this->get('page_size');` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `orderDetails`

订单详情（商品/收货/金额）。

> **类**：`zxf\XfAdmin\Components\Data\OrderDetails`
> **文件**：`src/Components/Data/OrderDetails.php`（121 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::orderDetails([
    'order' => [
        'id' => '#ORD-1001', 'date' => '2026-07-20', 'status' => 'completed',
        'customer' => ['name'=>'张三','email'=>'z@x.com','phone'=>'138…','address'=>'北京市…'],
        'items' => [['name'=>'商品A','sku'=>'SKU-1','qty'=>2,'price'=>'¥99','image'=>'products/1.png']],
        'subtotal' => '¥198','discount' => '-¥10','shipping' => '¥0','tax' => '¥0','total' => '¥188',
        'timeline' => [['title'=>'已下单','time'=>'07-20 10:00','done'=>true],['title'=>'已发货','time'=>'07-21','done'=>true]],
        'notes' => '请尽快发货',
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::orderDetails([
    'order' => [],    // 排序规则，如 `[[0, 'asc']]`
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `order[]` 元素键：`status`（默认 `pending`）、`id`、`status_text`、`items`、`timeline`、`customer`、`subtotal`、`discount`、`shipping`、`tax`、`total`、`notes`

> **渲染骨架**：主要 class `row` `g-3` `col-lg-8` `card` `mb-3` `card-header` `d-flex` `justify-content-between`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `order` | array | `[]` | 排序规则，如 `[[0, 'asc']]` |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `orderTrackingTimeline`

订单追踪时间线（物流状态流）。

> **类**：`zxf\XfAdmin\Components\Data\OrderTrackingTimeline`
> **文件**：`src/Components/Data/OrderTrackingTimeline.php`（48 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::orderTrackingTimeline([
    'steps' => [
        ['title'=>'已下单','desc'=>'2026-08-01 09:00','done'=>true],
        ['title'=>'已发货','desc'=>'2026-08-02 14:00','done'=>true],
        ['title'=>'运输中','desc'=>'2026-08-03 10:00','current'=>true],
        ['title'=>'已签收','desc'=>'','done'=>false],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::orderTrackingTimeline([
    'steps' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `steps[]` 元素键：`done`、`current`、`title`、`desc`

> **渲染骨架**：主要 class `xf-order-track` `d-flex` `justify-content-between` `position-relative` `xf-order-track-line` `position-absolute` `top-0` `start-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `steps` | array | `[]` | 步骤数组（向导 / 步骤条） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `purchasedOrders`

已购订单（买家视图）。

> **类**：`zxf\XfAdmin\Components\Data\PurchasedOrders`
> **文件**：`src/Components/Data/PurchasedOrders.php`（70 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::purchasedOrders([
    'orders' => [
        ['id' => '#PO-1001', 'supplier' => '供应商A', 'items' => 45, 'total' => 12500.00, 'date' => '2025-08-01', 'status' => 'received', 'deliveryDate' => '2025-08-08'],
        ...
    ],
    'currency' => '¥',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::purchasedOrders([
    'orders' => [],
    'currency' => '¥',
    'title' => '采购订单',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `orders[]` 元素键：`id`、`supplier`、`items`、`total`、`date`、`status`（默认 `pending`）、`deliveryDate`

> **渲染骨架**：主要 class `card` `card-header` `d-flex` `justify-content-between` `align-items-center` `gap-2` `card-body` `p-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `orders` | array | `[]` |  |
| `currency` | string | `'¥'` | 货币符号 |
| `title` | string | `'采购订单'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `refunds`

退款管理（退款单列表）。

> **类**：`zxf\XfAdmin\Components\Data\Refunds`
> **文件**：`src/Components/Data/Refunds.php`（75 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::refunds([
    'refunds' => [
        ['id' => '#REF-1001', 'orderId' => '#ORD-5001', 'customer' => '客户名', 'amount' => 89.00, 'reason' => '质量问题', 'date' => '2025-08-10', 'status' => 'pending', 'items' => 2],
        ...
    ],
    'currency' => '$',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::refunds([
    'refunds' => [],
    'currency' => '¥',
    'title' => '退款管理',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `refunds[]` 元素键：`id`、`orderId`、`customer`、`amount`、`reason`、`date`、`status`（默认 `pending`）、`items`

> **渲染骨架**：主要 class `card` `card-header` `d-flex` `justify-content-between` `align-items-center` `gap-2` `input-group` `input-group-sm`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `refunds` | array | `[]` | 退款单列表 |
| `currency` | string | `'¥'` | 货币符号 |
| `title` | string | `'退款管理'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `sales`

销售数据（图表+列表）。

> **类**：`zxf\XfAdmin\Components\Data\Sales`
> **文件**：`src/Components/Data/Sales.php`（105 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::sales([
    'stats' => [
        ['title' => '总收入', 'value' => '$128,430', 'icon' => 'ti-currency-dollar', 'color' => 'primary', 'change' => '+12.5%'],
        ['title' => '订单数', 'value' => '2,845', 'icon' => 'ti-shopping-cart', 'color' => 'success', 'change' => '+8.2%'],
        ['title' => '客户数', 'value' => '1,240', 'icon' => 'ti-users', 'color' => 'warning', 'change' => '+15.7%'],
        ['title' => '退款率', 'value' => '2.3%', 'icon' => 'ti-receipt-refund', 'color' => 'danger', 'change' => '-0.5%'],
    ],
    'recentOrders' => [
        ['id' => '#ORD-5001', 'customer' => '客户名', 'amount' => 89.00, 'date' => '2025-08-10', 'status' => 'completed'],
        ...
    ],
    'topProducts' => [
        ['name' => '商品名', 'sold' => 256, 'revenue' => 12800.00, 'growth' => 12],
        ...
    ],
    'currency' => '$',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::sales([
    'stats' => [],
    'recentOrders' => [],
    'topProducts' => [],
    'currency' => '¥',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `stats[]` 元素键：`color`（默认 `primary`）、`icon`（默认 `ti-trending-up`）、`title`、`value`、`change`
- `recentOrders[]` 元素键：`status`（默认 `completed`）、`id`、`customer`、`amount`、`date`
- `topProducts[]` 元素键：`growth`、`name`、`sold`、`revenue`

> **渲染骨架**：主要 class `row` `mb-4` `card` `card-body` `d-flex` `justify-content-between` `align-items-start` `text-muted`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `recentOrders` | array | `[]` | 最近订单列表 |
| `topProducts` | array | `[]` | 热销商品列表 |
| `currency` | string | `'¥'` | 货币符号 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `customers`

客户列表（电商客户管理）。

> **类**：`zxf\XfAdmin\Components\Data\Customers`
> **文件**：`src/Components/Data/Customers.php`（146 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::customers([
    'title' => '客户管理',
    'view'  => 'grid',            // grid | list
    'items' => [
        [
            'name' => '张三', 'email' => 'z@x.com', 'phone' => '138…',
            'avatar' => 'users/user-1.jpg', 'company' => 'XX 科技',
            'location' => '北京', 'status' => 'active', // active|vip|sleep
            'tags' => ['企业','复购'], 'orders' => 12, 'spent' => '¥1,200',
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::customers([
    'title' => '',
    'view' => 'grid',
    'searchable' => true,
    'items' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`status`（默认 `active`）、`avatar`、`tags`、`name`、`company`、`email`、`phone`、`location`、`orders`、`spent`（默认 `¥0`）

> **渲染骨架**：主要 class `row` `g-2` `align-items-center` `mb-3` `col-md-6` `position-relative` `text-md-end`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `view` | string | `'grid'` | 详情视图配置（viewRow 引擎，见数据表格文档）；源码用法：`$body = $this->get('view') === 'list' ? $this->renderList($items) : $this->renderGrid…` |
| `searchable` | bool | `true` | 是否参与搜索；开关：非空 / 真值时启用对应区块 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `sellers`

卖家列表（电商卖家管理）。

> **类**：`zxf\XfAdmin\Components\Data\Sellers`
> **文件**：`src/Components/Data/Sellers.php`（96 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::sellers([
    'title'      => '卖家管理',
    'searchable' => true,
    'sellers'    => [
        [
            'name'    => '北岸数码',
            'avatar'  => 'sellers/3.png',
            'products'=> 142,
            'orders'  => 3180,
            'rating'  => 4.8,
            'location'=> '深圳',
            'balance' => '¥1.28M',
            'rank'    => '#2',
            'status'  => ['text' => '活跃', 'variant' => 'success'],
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::sellers([
    'title' => '卖家管理',
    'searchable' => true,
    'add_text' => '新增卖家',
    'sellers' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `sellers[]` 元素键：`avatar`、`name`、`location`、`products`、`orders`、`rating`、`balance`、`rank`

> **渲染骨架**：主要 class `card-header` `border-light` `justify-content-between` `d-flex` `gap-2` `search-box` `card-body` `p-0`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'卖家管理'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `searchable` | bool | `true` | 是否参与搜索；开关：非空 / 真值时启用对应区块 |
| `add_text` | string | `'新增卖家'` | 「添加」按钮文案；**文本槽位**：输出前自动 HTML 转义 |
| `sellers` | array | `[]` | 卖家列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `sellerDetails`

卖家详情（店铺/商品/评价）。

> **类**：`zxf\XfAdmin\Components\Data\SellerDetails`
> **文件**：`src/Components/Data/SellerDetails.php`（148 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::sellerDetails([
    'seller' => [
        'name'     => '极物优选旗舰店',
        'logo'     => 'sellers/1.png',
        'verified' => true,
        'rating'   => 4.8,
        'desc'     => '专注数码周边十年。',
        'meta'     => [['icon' => 'ti ti-map-pin', 'text' => '深圳']],
    ],
    'stats'    => [['label' => '总销售额', 'value' => '¥128 万', 'icon' => 'ti ti-currency-yen', 'color' => 'primary']],
    'products' => [['name' => '蓝牙耳机', 'image' => 'products/2.png', 'price' => '¥299', 'stock' => 320, 'sales' => '1.2k', 'status' => ['text' => '在售', 'variant' => 'success']]],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::sellerDetails([
    'seller' => [],
    'stats' => [],
    'products' => [],
    'actions' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `seller[]` 元素键：`logo`、`name`、`verified`、`rating`、`desc`、`meta`
- `actions[]` 元素键：`onclick`、`icon`、`text`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `seller` | array | `[]` | 卖家信息 |
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `products` | array | `[]` | 商品数据数组 |
| `actions` | array | `[]` | 操作区内容（按钮组 / 行操作定义） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `reviewList`

评价列表（星级+内容+晒图）。

> **类**：`zxf\XfAdmin\Components\Data\ReviewList`
> **文件**：`src/Components/Data/ReviewList.php`（143 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::reviewList([
    'title'   => '商品评价',
    'summary' => [
        'avg'   => 4.92,
        'total' => 245,
        'dist'  => [
            ['star' => 5, 'count' => 128],
            ['star' => 4, 'count' => 24],
            ['star' => 3, 'count' => 15],
            ['star' => 2, 'count' => 8],
            ['star' => 1, 'count' => 5],
        ],
        'note'  => '来自真实购买用户的反馈',
        'badge' => '+12 new this week',
    ],
    'reviews' => [
        [
            'product_img'     => 'products/2.png',
            'product'         => 'Wireless Earbuds',
            'reviewer_avatar' => 'users/user-8.jpg',
            'reviewer'        => 'Sophia Lee',
            'reviewer_email'  => 'sophia.lee@digitalshop.com',
            'rating'          => 5,
            'comment'         => '音质出色，佩戴舒适。',
            'date'            => '2026-07-18',
            'status'          => ['text' => '已审核', 'variant' => 'success'],
        ],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::reviewList([
    'title' => '商品评价',
    'summary' => [],
    'reviews' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `summary[]` 元素键：`avg`、`total`、`note`、`badge`、`dist`
- `reviews[]` 元素键：`product_img`、`product`、`reviewer_avatar`、`reviewer`、`reviewer_email`、`rating`、`comment`、`date`、`status`

> **渲染骨架**：主要 class `row` `g-0` `align-items-center` `border-bottom` `border-light` `col-xl-6` `border-end` `border-dashed`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `title` | string | `'商品评价'` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义 |
| `summary` | array | `[]` | 摘要文本 |
| `reviews` | array | `[]` | 评价列表 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `attributes`

商品属性管理（规格键值）。

> **类**：`zxf\XfAdmin\Components\Data\Attributes`
> **文件**：`src/Components/Data/Attributes.php`（71 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::attributes([
    'attributes' => [
        ['name' => '颜色', 'slug' => 'color', 'type' => 'select', 'values' => ['红色', '蓝色', '黑色', '白色'], 'products' => 128],
        ['name' => '尺寸', 'slug' => 'size', 'type' => 'select', 'values' => ['S', 'M', 'L', 'XL'], 'products' => 256],
        ...
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::attributes([
    'attributes' => [],
    'title' => '产品属性',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `attributes[]` 元素键：`name`、`slug`、`type`（默认 `text`）、`values`、`products`

> **渲染骨架**：主要 class `card` `card-header` `d-flex` `justify-content-between` `align-items-center` `card-body` `p-0` `table-responsive`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `attributes` | array | `[]` | 属性列表（商品规格键值对） |
| `title` | string | `'产品属性'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `ecommerceSettings`

电商设置（通用配置表单）。

> **类**：`zxf\XfAdmin\Components\Data\EcommerceSettings`
> **文件**：`src/Components/Data/EcommerceSettings.php`（61 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::ecommerceSettings([
    'store' => [
        'name' => 'My Store',
        'email' => 'store@example.com',
        'phone' => '+86 138-xxxx-xxxx',
        'currency' => 'CNY',
        'timezone' => 'Asia/Shanghai',
    ],
    'sections' => [
        ['title' => '支付方式', 'content' => '<button class="btn btn-outline-primary">配置支付</button>'],
        ['title' => '配送设置', 'content' => '<div class="form-check"><input class="form-check-input" type="checkbox" checked><label>免运费（满 ¥99）</label></div>'],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::ecommerceSettings([
    'store' => [],
    'sections' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `store[]` 元素键：`name`、`email`、`phone`、`currency`（默认 `CNY`）、`timezone`（默认 `Asia/Shanghai`）
- `sections[]` 元素键：`title`、`content`

> **渲染骨架**：主要 class `row` `g-4` `col-lg-3` `card` `list-group` `list-group-flush` `col-lg-9` `card-body`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `store` | array | `[]` | 店铺数据 |
| `sections` | array | `[]` | 分区数组 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `ecommerceDashboard`

电商仪表盘（销售/订单/库存概览）。

> **类**：`zxf\XfAdmin\Components\Data\EcommerceDashboard`
> **文件**：`src/Components/Data/EcommerceDashboard.php`（247 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::ecommerceDashboard([
    'stats' => [
        ['label' => '今日订单', 'value' => 284, 'trend' => '+12.5%', 'icon' => 'ti-shopping-cart', 'color' => 'primary'],
        ['label' => '销售额', 'value' => 89420.50, 'trend' => '+8.2%', 'icon' => 'ti-currency-dollar', 'color' => 'success', 'currency' => true],
        ['label' => '访客数', 'value' => 12450, 'trend' => '+5.3%', 'icon' => 'ti-users', 'color' => 'info'],
        ['label' => '转化率', 'value' => 2.28, 'trend' => '-0.1%', 'icon' => 'ti-trending-up', 'color' => 'warning', 'suffix' => '%'],
    ],
    'recentOrders' => [
        ['id' => '#ORD-8921', 'customer' => '张三', 'amount' => 359.00, 'status' => '已完成', 'date' => '2025-01-15'],
        ...
    ],
    'topProducts' => [
        ['name' => '无线耳机 Pro', 'image' => 'products/1.png', 'sales' => 1250, 'revenue' => 89750.00],
        ...
    ],
    'chart' => true,  // 是否渲染图表区域
    'currency' => '¥',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::ecommerceDashboard([
    'stats' => [],
    'recentOrders' => [],
    'topProducts' => [],
    'chart' => true,
    'chartData' => null,    // 图表自定义数据数组；为 null 时生成占位演示数据
    'currency' => '¥',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `stats[]` 元素键：`label`、`value`、`trend`、`icon`（默认 `ti-chart-bar`）、`color`（默认 `primary`）、`currency`、`suffix`

> **渲染骨架**：主要 class `row` `g-3` `mb-4` `col-lg-8` `col-lg-4`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `stats` | array | `[]` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `recentOrders` | array | `[]` | 最近订单列表 |
| `topProducts` | array | `[]` | 热销商品列表 |
| `chart` | bool | `true` | 图表配置（类型 / 数据 / 颜色） |
| `chartData` | mixed | `null` | 图表自定义数据数组；为 null 时生成占位演示数据 |
| `currency` | string | `'¥'` | 货币符号 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `marketplace`

应用市场（插件/模板网格）。

> **类**：`zxf\XfAdmin\Components\Data\Marketplace`
> **文件**：`src/Components/Data/Marketplace.php`（210 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::marketplace([
    'categories' => [
        ['title' => 'For Men', 'icon' => 'ti-trending-up', 'bg' => 'success', 'children' => ['Sports suits', 'Trousers', 'Jackets and coats', 'Shirts'], 'image' => 'products/1.png'],
        ['title' => 'For Women', 'bg' => 'warning', 'children' => ['Dresses', 'Pants and jeans', 'Shirts and blouses', 'Sweatshirts'], 'image' => 'products/2.png'],
        ['title' => 'Accessories', 'bg' => 'danger', 'children' => ['Caps and hats', 'Sunglasses', 'Handbags', 'Jewelry'], 'image' => 'products/3.png'],
    ],
    'filters' => ['Best Sellers', 'New Arrived', 'Sale Items', 'Top Rated'],
    'products' => [
        ['title' => '商品名', 'image' => 'products/1.png', 'price' => 764.15, 'old_price' => 899.00, 'rating' => 3, 'reviews' => 45, 'badge' => '15% OFF', 'badge_color' => 'success'],
        // ...
    ],
    'currency' => '$',
    'columns' => [4, 3, 2, 1], // xl, lg, sm 列数
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::marketplace([
    'categories' => [],
    'filters' => [],
    'products' => [],
    'currency' => '$',
    // columns
    'columns' => [
        4,
        3,
        2,
        1,
    ],
    'subtitle' => 'Find Your Perfect Style',
    'subtitle_desc' => '👕 Discover styles tailored for everyone',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `categories[]` 元素键：`bg`（默认 `primary`）、`title`、`image`、`children`
- `products[]` 元素键：`title`、`image`、`price`、`old_price`、`rating`、`reviews`、`badge`、`badge_color`（默认 `danger`）

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `categories` | array | `[]` | 分类列表 |
| `filters` | array | `[]` | 过滤条件定义 |
| `products` | array | `[]` | 商品数据数组 |
| `currency` | string | `'$'` | 货币符号 |
| `columns` | array | `[4, 3, 2, 1]` | 列定义数组 |
| `subtitle` | string | `'Find Your Perfect Style'` | 副标题文本 |
| `subtitle_desc` | string | `'👕 Discover styles tailored for everyone'` | 副标题描述 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `warehouse`

仓库管理（库存/出入库）。

> **类**：`zxf\XfAdmin\Components\Data\Warehouse`
> **文件**：`src/Components/Data/Warehouse.php`（80 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::warehouse([
    'warehouses' => [
        ['name' => '主仓库', 'location' => '上海浦东', 'manager' => '张三', 'capacity' => 15000, 'used' => 10234, 'status' => 'active', 'products' => 345],
        ...
    ],
    'totalCapacity' => '45,000',
    'totalInventory' => '28,564',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::warehouse([
    'warehouses' => [],
    'totalCapacity' => '0',
    'totalInventory' => '0',
    'title' => '仓库管理',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `warehouses[]` 元素键：`name`、`location`、`manager`、`capacity`、`used`、`status`（默认 `active`）、`products`

> **渲染骨架**：主要 class `row` `mb-3` `col-md-6` `card` `text-bg-primary` `card-body` `text-bg-success` `card-header`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `warehouses` | array | `[]` | 仓库列表 |
| `totalCapacity` | string | `'0'` | 总容量 |
| `totalInventory` | string | `'0'` | 总库存 |
| `title` | string | `'仓库管理'` | 标题文本（部分组件为弹窗/tooltip 标题） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `invoiceCreate`

发票创建表单。

> **类**：`zxf\XfAdmin\Components\Data\InvoiceCreate`
> **文件**：`src/Components/Data/InvoiceCreate.php`（84 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::invoiceCreate([
    'invoice' => [
        'number' => 'INV-2026-001',
        'from' => ['name'=>'我方公司','address'=>'…','email'=>'billing@x.com'],
        'to' => ['name'=>'客户公司','address'=>'…','email'=>'c@x.com'],
        'items' => [['description'=>'网站设计','qty'=>1,'rate'=>'¥8000','amount'=>'¥8000']],
        'tax_rate' => 6, 'discount' => '¥0',
        'notes' => '…', 'terms' => '…',
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::invoiceCreate([
    'invoice' => [],    // 发票数据（单号 / 金额 / 状态等）
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `invoice[]` 元素键：`number`、`from`、`to`、`items`、`tax_rate`、`notes`、`terms`

> **渲染骨架**：主要 class `card` `card-body` `d-flex` `justify-content-between` `align-items-start` `flex-wrap` `gap-3` `mb-4`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `invoice` | array | `[]` | 发票数据（单号 / 金额 / 状态等） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `invoiceDetail`

发票详情整页。

> **类**：`zxf\XfAdmin\Components\Data\InvoiceDetail`
> **文件**：`src/Components/Data/InvoiceDetail.php`（121 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::invoiceDetail([
    'logo'     => '',                                       // 图片路径或留空显示标题
    'title'    => 'XF Admin',
    'number'   => 'INV-2026-0728',
    'status'   => ['text' => '待付款', 'color' => 'warning'],
    'meta'     => [['label' => '开票日期', 'value' => '2026-07-28']],
    'from'     => ['name' => '深圳某某科技', 'lines' => ['南山区…', 'tax@x.com']],
    'to'       => ['name' => '北京某某集团', 'lines' => ['朝阳区…']],
    'items'    => [['name' => '企业版授权', 'desc' => '1 年', 'qty' => 2, 'price' => 4999]],
    'currency' => '¥',
    'summary'  => [['label' => '小计', 'value' => '¥9,998.00'], ['label' => '合计', 'value' => '¥11,097.78', 'strong' => true]],
    'notes'    => '请于 15 日内完成付款。',
    'actions'  => [['text' => '打印', 'icon' => 'ti ti-printer', 'class' => 'btn-soft-secondary', 'onclick' => 'window.print()']],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::invoiceDetail([
    'logo' => '',
    'title' => '',
    'number' => '',
    'status' => [],
    'meta' => [],
    'from' => [],
    'to' => [],
    'items' => [],
    'currency' => '¥',
    'summary' => [],
    'notes' => '',
    'actions' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `status[]` 元素键：`color`（默认 `secondary`）、`text`
- `meta[]` 元素键：`label`、`value`
- `items[]` 元素键：`qty`、`price`、`amount`、`name`、`desc`
- `summary[]` 元素键：`strong`、`label`、`value`
- `actions[]` 元素键：`onclick`、`url`（默认 `javascript:;`）、`icon`、`text`

> **渲染骨架**：主要 class `card-body` `p-4` `d-flex` `flex-wrap` `justify-content-between` `align-items-start` `mb-4` `text-muted`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `logo` | string | `''` | 图片路径：外链 / `data:` URI 原样，其余解析为包内 `images/`；开关：非空 / 真值时启用对应区块 |
| `title` | string | `''` | 标题文本（部分组件为弹窗/tooltip 标题）；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `number` | string | `''` | **文本槽位**：输出前自动 HTML 转义 |
| `status` | array | `[]` | 状态值 / 状态映射 |
| `meta` | array | `[]` | 附加信息（时间 / 作者等） |
| `from` | array | `[]` | 起始值 / 来源地址 |
| `to` | array | `[]` | 结束值 / 目标地址 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `currency` | string | `'¥'` | 货币符号 |
| `summary` | array | `[]` | 摘要文本 |
| `notes` | string | `''` | 备注；**文本槽位**：输出前自动 HTML 转义；开关：非空 / 真值时启用对应区块 |
| `actions` | array | `[]` | 操作区内容（按钮组 / 行操作定义） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `invoiceList`

发票列表（列表+状态+金额）。

> **类**：`zxf\XfAdmin\Components\Data\InvoiceList`
> **文件**：`src/Components/Data/InvoiceList.php`（75 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::invoiceList([
    'items' => [
        [
            'id'      => 'INV-001',
            'client'  => '北京科技有限公司',
            'amount'  => 12800.00,
            'status'  => 'paid',          // paid | unpaid | pending | overdue
            'issued'  => '2026-07-01',
            'due'     => '2026-07-15',
            'actions' => '<a href="#" class="btn btn-sm btn-soft-primary">查看</a>',
        ],
    ],
    'currency' => '¥',
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::invoiceList([
    'items' => [],
    'currency' => '¥',
    'title' => '发票',
    'summary' => [],    // 顶部统计卡片：['label'=>,'value'=>,'variant'=>]
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`status`（默认 `未知`）、`amount`、`id`、`client`、`issued`、`due`、`actions`
- `summary[]` 元素键：`variant`（默认 `primary`）、`label`、`value`

> **渲染骨架**：主要 class `row` `g-3` `mb-3` `col-md-3` `col-6` `card` `border-0` `shadow-sm`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `currency` | string | `'¥'` | 货币符号；**文本槽位**：输出前自动 HTML 转义 |
| `title` | string | `'发票'` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `summary` | array | `[]` | 顶部统计卡片：['label'=>,'value'=>,'variant'=>] |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `invoiceTable`

发票明细表格。

> **类**：`zxf\XfAdmin\Components\Data\InvoiceTable`
> **文件**：`src/Components/Data/InvoiceTable.php`（62 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::invoiceTable([
    'items' => [
        ['name' => '产品A', 'desc' => '说明', 'qty' => 2, 'price' => 100, 'total' => 200],
    ],
    'currency' => '¥',
    'summary'  => [
        ['label' => '小计', 'value' => 200],
        ['label' => '合计', 'value' => 220, 'strong' => true],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::invoiceTable([
    'items' => [],
    'currency' => '¥',
    'summary' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`name`、`desc`、`qty`、`price`、`total`
- `summary[]` 元素键：`strong`、`label`、`value`

> **渲染骨架**：主要 class `table` `table-borderless` `text-nowrap` `mb-0` `d-flex` `justify-content-end` `mt-3` `w-auto`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `currency` | string | `'¥'` | 货币符号；**文本槽位**：输出前自动 HTML 转义 |
| `summary` | array | `[]` | 摘要文本 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `invoiceView`

发票查看（打印友好视图）。

> **类**：`zxf\XfAdmin\Components\Data\InvoiceView`
> **文件**：`src/Components/Data/InvoiceView.php`（94 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::invoiceView([
    'invoice_no' => 'INV-2026-0812',
    'issued_at'  => '2026-08-12',
    'due_at'     => '2026-09-11',
    'from'       => ['name' => 'WSF 科技有限公司', 'address' => '北京市朝阳区xx路1号', 'tax' => '91110105XXXX'],
    'to'         => ['name' => '示例客户有限公司', 'address' => '上海市浦东新区yy路2号', 'tax' => '91310115YYYY'],
    'items'      => [
        ['desc' => '企业版年度授权', 'qty' => 1, 'price' => 12800.00],
        ['desc' => '专属技术支持（年）', 'qty' => 1, 'price' => 3600.00],
    ],
    'tax_rate' => 0.06,
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::invoiceView([
    'invoice_no' => '',
    'issued_at' => '',
    'due_at' => '',
    'from' => [],
    'to' => [],
    'items' => [],
    'tax_rate' => 0.06,
    'currency' => '¥',
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `items[]` 元素键：`desc`、`qty`、`price`

> **渲染骨架**：主要 class `fw-semibold` `text-muted` `small` `card` `card-body` `d-flex` `justify-content-between` `align-items-start`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `invoice_no` | string | `''` | 发票号 |
| `issued_at` | string | `''` | 签发时间 |
| `due_at` | string | `''` | 到期时间 |
| `from` | array | `[]` | 起始值 / 来源地址 |
| `to` | array | `[]` | 结束值 / 目标地址 |
| `items` | array | `[]` | 条目数组（结构见各组件说明） |
| `tax_rate` | float | `0.06` | 税率 |
| `currency` | string | `'¥'` | 货币符号 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `filterSidebar`

筛选侧栏（分类/价格/标签过滤）。

> **类**：`zxf\XfAdmin\Components\Data\FilterSidebar`
> **文件**：`src/Components/Data/FilterSidebar.php`（87 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::filterSidebar([
    'groups' => [
        ['title'=>'分类','type'=>'tree','items'=>[['label'=>'数码','value'=>'digital','children'=>[...]]]],
        ['title'=>'价格','type'=>'price','min'=>0,'max'=>9999],
        ['title'=>'品牌','type'=>'check','items'=>[['label'=>'Apple','value'=>'apple'],['label'=>'小米','value'=>'mi']]],
    ],
    'button' => ['text'=>'应用筛选','variant'=>'primary'],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::filterSidebar([
    'groups' => [],
    // button
    'button' => [
        'text' => '应用筛选',
        'variant' => 'primary',
    ],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `groups[]` 元素键：`type`（默认 `check`）、`title`（默认 `x`）、`items`、`min`、`max`
- `button[]` 元素键：`variant`（默认 `primary`）、`text`（默认 `应用筛选`）

> **渲染骨架**：主要 class `xf-filter-sidebar` `card` `border-0` `shadow-sm` `card-header` `bg-transparent` `fw-semibold` `card-body`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `groups` | array | `[]` | 分组数据（下拉分组 / 权限分组 / 设置分组） |
| `button` | array | `['text'=>'应用筛选', 'variant'=>'primary']` | 数组结构（见「全参数示例」） |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

---

### `featureComparisonTable`

功能对比表（方案×特性矩阵）。

> **类**：`zxf\XfAdmin\Components\Data\FeatureComparisonTable`
> **文件**：`src/Components/Data/FeatureComparisonTable.php`（54 行）
> **依赖插件**：无

**用法示例**

```php
XfAdmin::featureComparisonTable([
    'plans' => ['基础版','专业版','企业版'],
    'featured' => 1,           // 高亮列索引
    'rows'  => [
        ['name'=>'项目数', 'values'=>['1','10','∞']],
        ['name'=>'数据导出', 'values'=>[false,true,true]],
        ['name'=>'SSO', 'values'=>[false,false,true]],
    ],
]);
```

<details><summary><b>全参数示例</b>（点击展开：列出该组件全部可配置参数，值均为默认值）</summary>

```php
echo XfAdmin::featureComparisonTable([
    'plans' => [],
    'featured' => null,
    'rows' => [],
]);
```

</details>

**数据结构**（数组元素可用键，由源码 `foreach` 解析）

- `rows[]` 元素键：`name`、`values`

> **渲染骨架**：主要 class `table-responsive` `table` `table-bordered` `align-middle` `text-center`

**配置参数**

| 参数 | 类型 | 默认值 | 说明 |
|---|---|---|---|
| `plans` | array | `[]` | 方案 / 计划列表 |
| `featured` | mixed | `null` | 源码用法：`$featured = $this->get('featured');` |
| `rows` | array | `[]` | 行数据数组 |

> 说明：`defaults()` 中以数组 `+` 合并的公共字段（如表单类的 `name`/`label`/`value`）见各组件所属基类说明；「内容槽位」原样输出 HTML，「文本槽位」自动转义。

