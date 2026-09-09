# 参数字典

> 本文件由 `php tools/gen_component_docs.php` 自动生成（源自生成器内的 `PARAM_DICT`）。
> 列出跨组件高频出现的配置键及其统一语义。具体组件的完整参数以各组件文档为准。

| 参数 | 说明 |
|---|---|
| `action` | 表单提交地址 / 动作类型 |
| `actions` | 操作区内容（按钮组 / 行操作定义） |
| `active` | 是否激活 / 默认选中项 |
| `address` | 地址 |
| `ajax` | AJAX 地址或 DataTables 原生 ajax 配置 |
| `align` | 对齐方式：start \| center \| end |
| `amount` | 金额数值 |
| `animation` | 动画类名 |
| `author` | 作者信息 |
| `autoplay` | 是否自动播放 |
| `avatar` | 头像地址（自动解析为包内图片 URL） |
| `badge` | 徽标文本或 `['text'=>..,'class'=>..]` |
| `body` | 主体内容（可为 HTML 字符串、组件实例或数组，原样输出） |
| `bordered` | 是否显示边框 |
| `borderless` | 是否无边框 |
| `brand` | 品牌信息（name/logo/url） |
| `bulk` | 批量操作配置 |
| `buttons` | 按钮定义数组 |
| `buttons` | 按钮组 |
| `caption` | 表格 caption 文本 |
| `category` | 分类 |
| `centered` | 是否垂直居中 |
| `children` | 子项数组（树形 / 菜单） |
| `class` | 附加到根元素的自定义 class |
| `close` | 是否显示关闭按钮 |
| `collapse` | 是否可折叠 |
| `color` | 颜色值（#hex / rgb() / 具名色） |
| `cols` | 列数（栅格 / 分区列数） |
| `columns` | 列定义数组 |
| `company` | 公司名 |
| `confirm` | 确认文案（非空则操作前确认） |
| `content` | 内容区（可为 HTML 字符串、组件实例或数组） |
| `count` | 数量 / 计数徽标数字 |
| `create` | 「新增」按钮配置 |
| `currency` | 货币符号 |
| `data` | 数据数组（行数据 / 图表数据） |
| `dataset` | 数据集标识（批量操作 / 领域动作提交给后端） |
| `date` | 日期文本 |
| `delay` | 延迟（毫秒） |
| `density` | 表格密度：compact 紧凑 |
| `department` | 部门 |
| `description` | 描述文本 |
| `direction` | 方向 |
| `disabled` | 是否禁用 |
| `dismiss` | 是否可关闭（警告条 / 模态框） |
| `driver` | 底层驱动（如编辑器 quill/summernote、上传 native/dropzone/filepond） |
| `duration` | 动画时长（毫秒） |
| `email` | 邮箱 |
| `empty` | 空数据提示文案 |
| `enhance` | 增强插件：choices \| select2 |
| `export` | 导出按钮：true 或 `['copy','excel','csv','pdf','print']` |
| `extra` | 附加内容（原样输出） |
| `fade` | 是否启用淡入动画 |
| `fields` | 字段定义数组（表单字段 / 详情字段） |
| `filters` | 过滤条件定义 |
| `filter_auto` | 过滤条件变更即自动查询 |
| `filter_bar` | 过滤工具条控件定义数组 |
| `fixed_columns` | 固定列：`true` 或 `['left'=>1,'right'=>1]` |
| `fixed_header` | 表头固定 |
| `footer` | 底部内容（原样输出） |
| `format` | 格式化闭包或格式字符串 |
| `format` | 格式化 |
| `gap` | 间距 |
| `group` | 分组信息 |
| `gutter` | 栅格间距（数字或 `['x'=>2,'y'=>3]`） |
| `header` | 头部内容（原样输出） |
| `height` | 高度（CSS 长度，受安全白名单约束） |
| `help` | 帮助说明文本（转义输出，渲染为 .form-text） |
| `hover` | 是否悬停高亮 |
| `href` | 链接地址（同 url） |
| `html` | 自定义 HTML（原样输出） |
| `icon` | Tabler 图标 class，如 `ti ti-user` |
| `icon_bg` | 图标背景色 |
| `id` | 根元素 id（留空自动生成唯一 id） |
| `image` | 图片地址（支持外链 / data URI / 包内 images 相对路径） |
| `inline` | 是否行内排列 |
| `interval` | 间隔时间（毫秒） |
| `items` | 条目数组（结构见各组件说明） |
| `justify` | 主轴对齐（start/center/end/between/around） |
| `key` | 字段名 / 键名（列定义中为数据字段） |
| `label` | 标签文案（表单字段标签 / 按钮文案） |
| `language` | 语言包覆盖（DataTables） |
| `layout` | 布局模式（各组件不同，如 vertical/horizontal） |
| `level` | 层级 / 级别 |
| `limit` | 最多展示条数 |
| `link` | 链接配置 |
| `links` | 链接数组（导航 / 底部链接） |
| `list` | 列表数据 |
| `loop` | 是否循环 |
| `max` | 最大值 |
| `menu` | 菜单数据数组 |
| `meta` | 附加信息（时间 / 作者等） |
| `method` | HTTP 方法（GET/POST/PUT/DELETE） |
| `min` | 最小值 |
| `mode` | 模式（各组件不同） |
| `multiple` | 是否多选 |
| `name` | 表单字段名 / 语义名称 |
| `note` | 备注文本 |
| `offset` | 栅格偏移 |
| `options` | 透传给底层插件的原生配置（递归合并，优先级最高） |
| `order` | 排序规则，如 `[[0, 'asc']]` |
| `orderable` | 是否可排序 |
| `ordering` | 是否允许排序 |
| `padding` | 是否保留内边距 |
| `page_length` | 每页条数 |
| `paging` | 是否分页 |
| `parent` | 父级信息 |
| `percent` | 百分比数值 |
| `phone` | 电话 |
| `placeholder` | 占位提示文案 |
| `placement` | 弹出方位：top \| bottom \| left \| right \| start \| end |
| `position` | 位置 |
| `price` | 价格数值 |
| `progress` | 进度百分比（0-100） |
| `qty` | 数量 |
| `quantity` | 数量 |
| `raw` | 是否原样输出（不转义） |
| `readonly` | 是否只读 |
| `reload` | 操作成功后是否刷新 |
| `remark` | 备注 |
| `render` | 单元格渲染器（字符串类型名或配置数组） |
| `required` | 是否必填（渲染 required 属性 + 红色星号） |
| `responsive` | 是否响应式（横向滚动 / 响应式表格） |
| `role` | 角色 |
| `rows` | 行数据数组 |
| `row_detail` | 行明细展开（true 或 `['columns'=>[..]]`） |
| `row_group` | 行分组字段名或 `['data'=>..,'empty'=>..]` |
| `scrollable` | 内容超长时是否内部滚动 |
| `scroll_x` | 横向滚动 |
| `scroll_y` | 纵向滚动高度 |
| `search` | 是否启用搜索 |
| `searchable` | 是否参与搜索 |
| `sections` | 分区数组 |
| `select` | 行选择：`true` 或 `['style'=>'multi']` |
| `server_side` | 服务端分页模式 |
| `show_footer` | 是否显示底部 |
| `show_header` | 是否显示头部 |
| `show_title` | 是否显示标题 |
| `size` | 尺寸：sm \| lg（部分组件支持 md/xl） |
| `sku` | 商品编码 |
| `sm` | 是否紧凑尺寸（table-sm / 小型） |
| `sort` | 排序号 |
| `sortable` | 是否可排序（orderable 别名） |
| `src` | 资源地址（图片 / iframe / 文件） |
| `state_save` | 保存表格状态（分页/排序/搜索） |
| `static` | 点击遮罩不关闭（静态背景） |
| `status` | 状态值 / 状态映射 |
| `step` | 步长 |
| `steps` | 步骤数组（向导 / 步骤条） |
| `stock` | 库存 |
| `striped` | 是否斑马纹 |
| `style` | 附加到根元素的内联样式 |
| `sub` | 副标题 / 附属信息 |
| `subtitle` | 副标题文本 |
| `summary` | 摘要文本 |
| `tabs` | 选项卡数组 |
| `tags` | 标签数组 / 是否启用标签输入 |
| `target` | 链接打开方式，如 _blank |
| `template` | 模板字符串，支持 `{field}` 占位 |
| `text` | 正文/按钮文案（纯文本语义，输出时转义） |
| `theme` | 主题（light / dark / 图表主题名） |
| `time` | 时间文本 |
| `title` | 标题文本（部分组件为弹窗/tooltip 标题） |
| `toolbar` | 是否显示工具条 |
| `tools` | 卡片工具按钮：`['collapse','refresh','close']` 或自定义 HTML |
| `total` | 合计数值 |
| `trend` | 趋势值（正/负） |
| `trigger` | 触发方式（hover / click / focus），或触发按钮文案 |
| `type` | 类型（各组件语义不同，详见该组件说明） |
| `url` | 链接地址（自动做安全协议校验） |
| `user` | 用户信息（name/avatar/email/role 等） |
| `value` | 当前值（表单控件值 / 展示数值） |
| `variant` | 语义变体：primary/secondary/success/danger/warning/info/light/dark（受白名单约束） |
| `vertical` | 是否纵向排列 |
| `visible` | 是否可见 |
| `width` | 宽度（数字=栅格列数或 CSS 长度） |
| `wrap` | 是否换行 |
