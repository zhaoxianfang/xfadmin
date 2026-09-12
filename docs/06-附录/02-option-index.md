# 参数字典

> 本文件由 `php tools/gen_component_docs.php` 自动生成（源自生成器内的 `PARAM_DICT`）。
> 列出跨组件高频出现的配置键及其统一语义。具体组件的完整参数以各组件文档为准。

| 参数 | 说明 |
|---|---|
| `action` | 表单提交地址 / 动作类型 |
| `actions` | 操作区内容（按钮组 / 行操作定义） |
| `active` | 是否激活 / 默认选中项 |
| `activeTab` | 默认激活的选项卡 id |
| `address` | 地址 |
| `add_text` | 「添加」按钮文案 |
| `afterForm` | 插入到 </form> 之后的内容（原样输出） |
| `ajax` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `alias` | 掩码别名（部分驱动支持） |
| `align` | 对齐方式：start \| center \| end |
| `align_middle` | 单元格垂直居中 |
| `allow` | 允许的行为（如允许的文件类型 / 操作） |
| `alt` | 图片替代文本 |
| `amount` | 金额数值 |
| `animation` | 动画类名 |
| `append` | 后缀内容（原样输出，常用于协议说明） |
| `apps` | 应用列表（应用启动器） |
| `article` | 文章数据（标题 / 正文 / 作者 / 封面等） |
| `attributes` | 属性列表（商品规格键值对） |
| `author` | 作者信息 |
| `autoplay` | 是否自动播放 |
| `auto_width` | 自动列宽（false 会影响 scrollX 判定） |
| `avatar` | 头像地址（自动解析为包内图片 URL） |
| `backLink` | 返回链接配置（保留兼容） |
| `badge` | 徽标文本或 `['text'=>..,'class'=>..]` |
| `bars` | 柱状数据数组 |
| `beforeForm` | 插入到 <form> 之前的内容（原样输出） |
| `below` | 表单下方补充内容（原样输出） |
| `bio` | 个人简介 |
| `blocks` | 内容区块数组 |
| `body` | 主体内容（可为 HTML 字符串、组件实例或数组，原样输出） |
| `bodyClass` | 追加到 <form> 的 class |
| `bordered` | 是否显示边框 |
| `borderless` | 是否无边框 |
| `bottom` | 底部内容 / 底部间距 |
| `brand` | 品牌信息（name/logo/url） |
| `brands` | 品牌列表 |
| `bulk` | 批量操作配置 |
| `buttons` | 按钮定义数组 |
| `buttons` | 按钮组 |
| `calendar` | 日历配置 / 是否启用日历 |
| `captcha` | 验证码：`false` 不显示 / 字符串原样输出 / `true` 输出占位 |
| `caption` | 表格 caption 文本 |
| `card` | 是否以卡片容器呈现（部分组件为遗留键） |
| `categories` | 分类列表 |
| `category` | 分类 |
| `center` | 地图/图表中心坐标 |
| `centered` | 是否垂直居中 |
| `chart` | 图表配置（类型 / 数据 / 颜色） |
| `charts` | 多个图表配置数组 |
| `children` | 子项数组（树形 / 菜单） |
| `class` | 附加到根元素的自定义 class |
| `clients` | 客户端 / 客户列表 |
| `clipboard` | 剪贴板配置 / 是否启用复制 |
| `close` | 是否显示关闭按钮 |
| `col` | 列宽（栅格列数 1-12） |
| `colClass` | 列 class（栅格） |
| `collapse` | 是否可折叠 |
| `collapsible` | 是否可折叠 |
| `color` | 颜色值（#hex / rgb() / 具名色） |
| `cols` | 列数（栅格 / 分区列数） |
| `columns` | 列定义数组 |
| `column_filters` | 表头追加列筛选输入行 |
| `compact` | 紧凑模式 |
| `companies` | 公司数据数组 |
| `company` | 公司名 |
| `confirm` | 确认文案（非空则操作前确认） |
| `connect` | noUiSlider connect 配置（数组自动为 true） |
| `contact` | 联系信息（渲染为 mailto 链接） |
| `contactEmail` | 联系邮箱 |
| `contacts` | 联系人列表 |
| `container` | 内容区容器 class（默认 container-fluid） |
| `content` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `controls` | 控件配置（播放器 / 轮播控件等） |
| `conversations` | 会话列表（聊天） |
| `copyright` | 版权文案 |
| `count` | 数量 / 计数徽标数字 |
| `cover` | 封面图地址 |
| `create` | 「新增」按钮配置 |
| `created_row` | 行创建回调（全局 JS 函数名） |
| `csrf` | 是否注入 CSRF 隐藏域（`true` 注入；`[]`/`false` 不注入；数组为自定义隐藏域） |
| `currency` | 货币符号 |
| `currentLocale` | 当前语言 |
| `currentStep` | 当前步骤索引（向导 / 步骤条） |
| `current_url` | 当前 URL（用于菜单自动高亮） |
| `customizer` | 是否渲染主题定制面板 |
| `data` | 数据数组（行数据 / 图表数据） |
| `dataset` | 数据集标识（批量操作 / 领域动作提交给后端） |
| `dataTable` | 内嵌数据表格配置 |
| `dataTableToolbar` | 内嵌表格工具条配置 |
| `date` | 日期文本 |
| `deadline` | 截止时间（倒计时目标，任意可被 strtotime 解析的字符串） |
| `defer_render` | 延迟渲染（本地数据 ≥100 行自动开启） |
| `delay` | 延迟（毫秒） |
| `density` | 表格密度：compact 紧凑 |
| `department` | 部门 |
| `description` | 描述文本 |
| `direction` | 方向 |
| `disabled` | 是否禁用 |
| `discount` | 折扣（金额或百分比） |
| `dismiss` | 是否可关闭（警告条 / 模态框） |
| `draw_callback` | 绘制完成回调（全局 JS 函数名） |
| `driver` | 底层驱动（如编辑器 quill/summernote、上传 native/dropzone/filepond） |
| `due_at` | 到期时间 |
| `duration` | 动画时长（毫秒） |
| `edges` | 边（桑基图 / 关系图的连线数据） |
| `effectiveDate` | 生效日期 |
| `email` | 邮箱 |
| `empty` | 空数据提示文案 |
| `enhance` | 增强插件：choices \| select2 |
| `enhance_options` | 透传给增强插件（choices/select2）的原生配置 |
| `events` | 事件数组（日历 / 时间线） |
| `export` | 导出按钮：true 或 `['copy','excel','csv','pdf','print']` |
| `externalEvents` | 外部可拖拽事件（日历） |
| `extra` | 附加内容（原样输出） |
| `fade` | 是否启用淡入动画 |
| `features` | 特性 / 功能列表 |
| `feedback` | 校验反馈文案 `['valid'=>..,'invalid'=>..]` |
| `fields` | 字段定义数组（表单字段 / 详情字段） |
| `files` | 文件列表 |
| `filters` | 过滤条件定义 |
| `filter_auto` | 过滤条件变更即自动查询 |
| `filter_bar` | 过滤工具条控件定义数组 |
| `fixed_columns` | 固定列：`true` 或 `['left'=>1,'right'=>1]` |
| `fixed_header` | 表头固定 |
| `flush` | 是否无边框（list-group-flush） |
| `folders` | 文件夹列表（文件管理器） |
| `footable` | 表尾行数据 |
| `footer` | 底部内容（原样输出） |
| `footerLinks` | 页脚链接 `[['url'=>..,'text'=>..]]` |
| `footer` | 页脚 |
| `format` | 格式化闭包或格式字符串 |
| `format` | 格式化 |
| `from` | 起始值 / 来源地址 |
| `gap` | 间距 |
| `group` | 分组信息 |
| `groups` | 分组数据（下拉分组 / 权限分组 / 设置分组） |
| `gutter` | 栅格间距（数字或 `['x'=>2,'y'=>3]`） |
| `head` | </head> 前附加内容（原样输出） |
| `header` | 头部内容（原样输出） |
| `headerBg` | 表头背景语义色（如 `primary`） |
| `head_class` | 追加到 thead 的 class |
| `height` | 高度（CSS 长度，受安全白名单约束） |
| `help` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `hint` | 输入框下方提示文本 |
| `horizontal` | 是否水平排列 |
| `hover` | 是否悬停高亮 |
| `href` | 链接地址（同 url） |
| `html` | 自定义 HTML（原样输出） |
| `icon` | Tabler 图标 class，如 `ti ti-user` |
| `icons` | 图标列表 |
| `icon_bg` | 图标背景色 |
| `id` | 根元素 id（留空自动生成唯一 id） |
| `image` | 图片地址（支持外链 / data URI / 包内 images 相对路径） |
| `info` | 是否显示分页信息 |
| `initial` | 初始值（编辑器 / 上传组件的已有内容） |
| `inline` | 是否行内排列 |
| `interval` | 间隔时间（毫秒） |
| `intro` | 导语 / 简介文本 |
| `invoice` | 发票数据（单号 / 金额 / 状态等） |
| `invoice_no` | 发票号 |
| `issued_at` | 签发时间 |
| `issues` | 问题 / 工单列表 |
| `items` | 条目数组（结构见各组件说明） |
| `justify` | 主轴对齐（start/center/end/between/around） |
| `key` | 字段名 / 键名（列定义中为数据字段） |
| `keys` | 键名数组 |
| `label` | 标签文案（表单字段标签 / 按钮文案） |
| `labels` | 标签或文案数组（组件语义不同：按钮文案 / 单位文案 / 图表标签） |
| `language` | 语言包覆盖（DataTables） |
| `layout` | 布局模式（各组件不同，如 vertical/horizontal） |
| `length` | 长度 / 位数（如 OTP 格数 4-8） |
| `length_menu` | 每页条数选项 |
| `level` | 层级 / 级别 |
| `lightbox` | 灯箱配置 / 是否启用灯箱 |
| `limit` | 最多展示条数 |
| `link` | 链接配置 |
| `links` | 链接数组（导航 / 底部链接） |
| `list` | 列表数据 |
| `locales` | 语言 / 区域列表 |
| `loginRedirect` | 「去登录」链接地址 |
| `loop` | 是否循环 |
| `map` | 地图名称（如 world） |
| `markers` | 地图标记点数组 |
| `mask` | 输入掩码表达式，如 `999-9999-9999` |
| `max` | 最大值 |
| `maxApps` | 最多显示的应用数量 |
| `me` | 当前用户（聊天气泡定位自己） |
| `members` | 成员列表 |
| `menu` | 菜单数据数组 |
| `messages` | 消息列表（聊天/通知/消息中心条目） |
| `meta` | 附加信息（时间 / 作者等） |
| `method` | HTTP 方法（GET/POST/PUT/DELETE） |
| `min` | 最小值 |
| `minScore` | 最低分数要求（低于则禁用提交按钮） |
| `mode` | 模式（各组件不同） |
| `multiple` | 是否多选 |
| `muted` | 次要 / 弱化显示 |
| `name` | 表单字段名 / 语义名称 |
| `nav` | 导航项数组 |
| `nestable` | 可拖拽排序配置 / 是否启用 |
| `nodes` | 节点数据（桑基图 / 关系图） |
| `node_height` | 节点高度（树图，px） |
| `node_width` | 节点宽度（树图，px） |
| `note` | 备注文本 |
| `notes` | 备注 |
| `offset` | 栅格偏移 |
| `options` | 透传给底层插件的原生配置（递归合并，优先级最高） |
| `options` | 配置项 |
| `order` | 排序规则，如 `[[0, 'asc']]` |
| `orderable` | 是否可排序 |
| `ordering` | 是否允许排序 |
| `padding` | 是否保留内边距 |
| `page_length` | 每页条数 |
| `page_title` | 页面标题区（数组则渲染 PageTitle 组件） |
| `paging` | 是否分页 |
| `parent` | 父级信息 |
| `pdfViewer` | PDF 预览配置 |
| `peer` | 对方（会话对象）信息 |
| `percent` | 百分比数值 |
| `permissions` | 权限列表 |
| `phone` | 电话 |
| `placeholder` | 占位提示文案 |
| `placement` | 弹出方位：top \| bottom \| left \| right \| start \| end |
| `plans` | 方案 / 计划列表 |
| `plugins` | 启用的插件列表 |
| `position` | 位置 |
| `poster` | 视频封面图 |
| `posts` | 文章 / 帖子列表 |
| `preloader` | 是否显示首屏加载动画 |
| `prepend` | 前缀内容（原样输出，如输入组文本/图标） |
| `price` | 价格数值 |
| `pricing` | 价格方案配置 |
| `processing` | 是否显示加载遮罩 |
| `product` | 商品数据 |
| `products` | 商品数据数组 |
| `progress` | 进度百分比（0-100） |
| `progress` | 进度 |
| `project` | 项目数据 |
| `projects` | 项目列表 |
| `provider` | 服务提供者 / 地图瓦片源 |
| `qty` | 数量 |
| `quantity` | 数量 |
| `question` | 验证码题目（math 模式） |
| `ranges` | 日期快捷区间（今天/昨天/最近7天/最近30天/本月/上月） |
| `ratio` | 宽高比（如 `16/9`） |
| `raw` | 是否原样输出（不转义） |
| `readonly` | 是否只读 |
| `recentActivity` | 最近活动列表 |
| `recentOrders` | 最近订单列表 |
| `refreshable` | 验证码可刷新（换一张） |
| `refunds` | 退款单列表 |
| `registerRedirect` | 「去注册」链接地址 |
| `reload` | 操作成功后是否刷新 |
| `remark` | 备注 |
| `render` | 单元格渲染器（字符串类型名或配置数组） |
| `required` | 是否必填（渲染 required 属性 + 红色星号） |
| `responsive` | 是否响应式（横向滚动 / 响应式表格） |
| `reviews` | 评价列表 |
| `role` | 角色 |
| `roles` | 角色列表（`id`+`name` 或键值形式） |
| `rows` | 行数据数组 |
| `row_attrs` | 行属性回调 `fn($row): array`，返回 `<tr>` 属性 |
| `row_detail` | 行明细展开（true 或 `['columns'=>[..]]`） |
| `row_group` | 行分组字段名或 `['data'=>..,'empty'=>..]` |
| `row_id` | 行 DOM id 字段 |
| `scripts` | </body> 前附加内容（原样输出） |
| `scrollable` | 内容超长时是否内部滚动 |
| `scroll_x` | 横向滚动 |
| `scroll_y` | 纵向滚动高度 |
| `search` | 是否启用搜索 |
| `searchable` | 是否参与搜索 |
| `sections` | 分区数组 |
| `select` | 行选择：`true` 或 `['style'=>'multi']` |
| `selected` | 是否选中 / 选中值 |
| `seller` | 卖家信息 |
| `sellers` | 卖家列表 |
| `series` | 图表数据系列 |
| `server_side` | 服务端分页模式 |
| `set` | 集合 / 预设值 |
| `shipping` | 运费 |
| `showBackToTop` | 是否显示「回到顶部」按钮 |
| `showBuiltin` | 是否显示内置区块 |
| `showRules` | 是否展示密码规则清单 |
| `show_custom_search` | 启用自定义搜索占位 |
| `show_footer` | 是否显示底部 |
| `show_header` | 是否显示头部 |
| `show_title` | 是否显示标题 |
| `sideImage` | 侧栏背景图（包内图片名 / 外链 / data URI / `/` 开头路径） |
| `sideImageAlt` | 侧栏背景图无障碍文本 |
| `sideImagePosition` | 侧栏背景 background-position |
| `sideImageSize` | 侧栏背景 background-size |
| `sideList` | 侧栏要点列表 `['icon'=>..,'text'=>..]` |
| `sideOverlay` | 侧栏是否显示渐变遮罩 |
| `sideText` | 侧栏说明文本 |
| `sideTitle` | 侧栏主标题 |
| `sideVariant` | 侧栏语义变体（primary/info/success/…） |
| `signature` | 个性签名（两行截断） |
| `single` | 单日期模式（singleDatePicker） |
| `size` | 尺寸：sm \| lg（部分组件支持 md/xl） |
| `sku` | 商品编码 |
| `sm` | 是否紧凑尺寸（table-sm / 小型） |
| `socialButtons` | 社交登录按钮 `['icon'=>..,'url'=>..,'label'=>..]` |
| `soft` | 柔和（浅色底）样式 |
| `sort` | 排序号 |
| `sortable` | 是否可排序（orderable 别名） |
| `src` | 资源地址（图片 / iframe / 文件） |
| `state_save` | 保存表格状态（分页/排序/搜索） |
| `static` | 点击遮罩不关闭（静态背景） |
| `stats` | 统计指标数组（如 `[['value'=>..,'label'=>..]]`） |
| `status` | 状态值 / 状态映射 |
| `step` | 步长 |
| `steps` | 步骤数组（向导 / 步骤条） |
| `stock` | 库存 |
| `store` | 店铺数据 |
| `striped` | 是否斑马纹 |
| `style` | 附加到根元素的内联样式 |
| `sub` | 副标题 / 附属信息 |
| `subheading` | 副标题（回退历史字段 subtitle） |
| `submit` | 提交按钮配置（字符串或数组：text/class/variant/icon） |
| `subscribe` | 是否显示订阅表单 |
| `subtitle` | 副标题文本 |
| `subtitle_desc` | 副标题描述 |
| `subtotal` | 小计金额 |
| `sub_categories` | 子分类列表 |
| `suffix` | 后缀文本 |
| `summary` | 摘要文本 |
| `sweetAlert` | SweetAlert 弹窗配置 |
| `table` | 关联表格 id 或表格配置 |
| `tablesCustom` | 内嵌自定义表格配置 |
| `tabs` | 选项卡数组 |
| `tag` | 标签 / 渲染标签名 |
| `tags` | 标签数组 / 是否启用标签输入 |
| `target` | 目标（组件语义不同：链接打开方式 `_blank` / 倒计时目标时间 / 数值目标） |
| `tax` | 税费 |
| `tax_rate` | 税率 |
| `teams` | 团队列表 |
| `template` | 模板字符串，支持 `{field}` 占位 |
| `testimonials` | 用户证言列表 |
| `text` | 正文/按钮文案（纯文本语义，输出时转义） |
| `textDiff` | 文本差异对比配置 |
| `theme` | 主题（light / dark / 图表主题名） |
| `thread` | 会话 / 主题贴数据 |
| `tiles` | 地图瓦片地址（null 时离线空白底图） |
| `time` | 时间文本 |
| `timepicker` | 是否带时间选择 |
| `tinycon` | favicon 角标配置 |
| `title` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `to` | 结束值 / 目标地址 |
| `toc` | 目录（Table of Contents）列表 |
| `toolbar` | 是否显示工具条 |
| `tools` | 卡片工具按钮：`['collapse','refresh','close']` 或自定义 HTML |
| `tooltips` | 滑块是否显示数值气泡 |
| `topProducts` | 热销商品列表 |
| `total` | 合计数值 |
| `totalCapacity` | 总容量 |
| `totalInventory` | 总库存 |
| `totalUnique` | 独立访客总数 |
| `totalViews` | 浏览总数 |
| `tour` | 引导漫游配置（步骤数组） |
| `treeView` | 树形视图数据 |
| `trend` | 趋势值（正/负） |
| `trend_text` | 趋势说明文案（如「较上周」） |
| `trigger` | 触发方式（hover / click / focus），或触发按钮文案 |
| `trigger_variant` | 触发按钮的语义变体（primary/secondary/…） |
| `type` | 类型（各组件语义不同，详见该组件说明） |
| `type_filter` | 类型筛选条件 |
| `url` | 链接地址（自动做安全协议校验） |
| `user` | 用户信息（name/avatar/email/role 等） |
| `users` | 用户列表 |
| `value` | 当前值（表单控件值 / 展示数值） |
| `values` | 数值集合（图表/表单默认值/矩阵勾选值） |
| `variant` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `vertical` | 是否纵向排列 |
| `view` | 详情视图配置（viewRow 引擎，见数据表格文档） |
| `views` | 浏览量数据 |
| `visible` | 是否可见 |
| `warehouses` | 仓库列表 |
| `whitelist` | 标签输入候选词数组 |
| `width` | 宽度（数字=栅格列数或 CSS 长度） |
| `wrap` | 是否换行 |
| `wrapper` | 外层包裹容器 class（`false`/`null` 时不包裹） |
| `zoom` | 地图缩放级别 |
