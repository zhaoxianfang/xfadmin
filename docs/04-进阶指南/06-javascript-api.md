# 前端 JS API

`resources/assets/js/xfadmin.js`（6341 行）是包的前端运行时，暴露全局对象 `window.XFAdmin`。

## 1. 对象属性

| 属性 | 类型 | 说明 |
|---|---|---|
| `XFAdmin.version` | string | 版本号 |
| `XFAdmin.widgets` | Object | 已注册的初始化器（`register` 写入，`scan` 读取） |
| `XFAdmin.destroyers` | Object | 销毁钩子 |
| `XFAdmin.instances` | WeakMap | 元素 → 实例 |
| `XFAdmin._meta` | WeakMap | 元素 → 组件名 |
| `XFAdmin._loaded` | Object | 资源去重表（URL → Promise） |
| `XFAdmin.cellEventHandlers` | Object | `onCell` 注册的处理器 |
| `XFAdmin.commandHandlers` | Object | `onCommand` 注册的命令 |
| `XFAdmin.uploadHandlers` | Object | `onUpload` 注册的上传回调 |
| `XFAdmin.cellRenderers` | Object | 单元格渲染器（60+ 项） |
| `XFAdmin.dtLanguage` | Object | DataTables 中文语言包 |

## 2. 生命周期与扫描

| API | 签名 | 说明 |
|---|---|---|
| `onReady` | `onReady(fn)` | DOM 就绪后执行；已就绪则同步执行 |
| `scan` | `scan(root?)` | 扫描 `root`（默认 document）内 `[data-xf]` 并初始化 |
| `get` | `get(el\|selector)` | 取元素对应的实例 |
| `destroy` | `destroy(el)` | 销毁单个组件 |
| `destroyWithin` | `destroyWithin(root?)` | 销毁子树内全部组件（AJAX 替换内容前调用） |
| `register` | `register(name, initFn, destroyFn?)` | 注册 widget |
| `initAuthPage` | `initAuthPage()` | 认证页专属初始化（脚本加载时自执行一次） |

```js
XFAdmin.register('myWidget', function (el, cfg) {
    const timer = setInterval(() => tick(el), 1000);
    return { destroy() { clearInterval(timer); } };
});

// AJAX 注入新内容
XFAdmin.destroyWithin(oldNode);
container.innerHTML = html;
XFAdmin.scan(container);
```

`scan` 流程：

```
root.querySelectorAll('[data-xf]')
  → 跳过 el.__xfInited === true
  → fn = XFAdmin.widgets[name]（不存在则静默跳过）
  → instance = fn(el, JSON.parse(el.dataset.xfConfig || '{}'))
  → 成功：__xfInited = true，写入 _meta / instances
  → 失败：__xfInited = false（允许重试）+ console.error
  → 末尾：initBootstrapExtras(root)（含 bindRemoteForms + tooltip/popover + needs-validation）
```

## 3. 资源动态加载

| API | 签名 | 说明 |
|---|---|---|
| `loadStyle` | `loadStyle(href): Promise` | 已存在同名 link 则直接 resolve |
| `loadScript` | `loadScript(src): Promise` | 已存在同名 script 则直接 resolve |
| `load` | `load(cssArr, jsArr): Promise` | CSS 并行 + JS **串行** |

```js
XFAdmin.load(['/p/select2/select2.min.css'],
             ['/p/jquery/jquery.min.js', '/p/select2/select2.min.js'])
  .then(() => XFAdmin.scan(root));
```

## 4. 工具方法

| API | 签名 | 说明 |
|---|---|---|
| `escapeHtml` | `escapeHtml(v)` | 转义 `& < > " '` |
| `tpl` | `tpl(tplStr, row)` | `{field}` / `{a.b.c}` 插值（**值经转义**） |
| `tplRaw` | `tplRaw(tplStr, row)` | 同上但不转义 |
| `csrf` | `csrf()` | 读 `<meta name="csrf-token">` |
| `copyText` | `copyText(text, tip?)` | 复制（clipboard API → textarea 回退） |
| `toast` | `toast({body, variant, delay})` | 轻提示（右上角，默认 4s 消失） |
| `countUp` | `countUp(el, target, {decimals, duration})` | 数字滚动动画 |
| `timelineHtml` | `timelineHtml(list)` | 生成时间线 HTML |
| `setLoading` | `setLoading(btn, on)` | 忙碌按钮切换 |

```js
XFAdmin.toast({ body: '保存成功', variant: 'success' });
XFAdmin.copyText('abc', '已复制');
```

## 5. 网络请求

```js
XFAdmin.request('/admin/api/x', {
    method: 'POST',        // 默认 GET；非 GET/HEAD 自动带 X-CSRF-TOKEN
    data: { a: 1 },        // 普通对象 → JSON；FormData → 原样 body
    headers: {},
    raw: false,            // true → 返回 {__raw: text}
    silent: false,         // true → 失败不弹 toast
    timeout: 0,            // >0 → AbortController 超时
}).then(res => {
    // res: {ok, status, data, errors, raw?, error?}
    if (res.ok) console.log(res.data);
});
```

⚠️ 只接受 `opts.data`，**不接受 `opts.body`**；异常永不 reject（超时 408、网络错误 status 0）。

## 6. 表单处理

| API | 签名 | 说明 |
|---|---|---|
| `handleFormResponse` | `handleFormResponse(res, {form, onSuccess, onError, noReload})` | 统一成功/失败处理 |
| `handleRemoteForm` | `handleRemoteForm(form, {tableEl, reload, modal})` | 接管单个表单（幂等） |
| `bindRemoteForms` | `bindRemoteForms(root?)` | 批量接管 `form[data-xf-remote]` |
| `bindLoadingButtons` | `bindLoadingButtons(root?)` | 绑定 `.xf-lbtn` 忙碌按钮 |

成功优先级：`data.url`（3s 后跳转）→ `data-xf-redirect`（1.2s）→ `data-xf-reset="1"` →
`location.reload()`（1.2s，`reload` 为真时）。

## 7. 弹窗与对话框

| API | 签名 | 说明 |
|---|---|---|
| `confirm` | `confirm(msg\|opts, onOk?, onCancel?): Promise<boolean>` | SweetAlert2 → Bootstrap Modal → `window.confirm` |
| `prompt` | `prompt(msg\|opts, onOk?, onCancel?): Promise<value>` | 单字段输入 |
| `promptFields` | `promptFields({title, fields, onOk})` | 多字段表单 |
| `popoverConfirm` | `popoverConfirm(anchor, msg, onConfirm)` | 气泡确认卡 |
| `dialog` | `dialog({title, size, body, footer}): Modal` | 通用只读弹窗 |
| `formDialog` | `formDialog({title, size, fields, values, onSubmit(data, done)})` | 表单弹窗；`done(false)` 保留 |
| `pageDialog` | `pageDialog(url\|html, opts)` | 拉取页面注入弹窗 |
| `editPage` / `createPage` | `editPage(url, opts)` | `pageDialog` 语义别名 |
| `disposeModal` | `disposeModal(id)` | 安全移除弹窗并清理 backdrop 残留 |

```js
XFAdmin.confirm('确认删除？', () => doDelete());

XFAdmin.pageDialog('/admin/users/1/edit', {
    title: '编辑用户', size: 'lg', reload: true, tableEl: '#user-table',
});

XFAdmin.formDialog({
    title: '调整额度',
    fields: [
        { name: 'amount', label: '金额', type: 'number', required: true },
        { name: 'reason', label: '原因', type: 'textarea' },
    ],
    onSubmit(data, done) {
        XFAdmin.request('/api/adjust', { method: 'POST', data })
            .then(r => { if (r.ok) done(); else done(false); });
    },
});
```

`pageDialog` 内容提取：优先 `[data-xf-page-content]`，回退 `main` / `.content-page` / `.card` / `.container-fluid`。
关闭时按 `__xfSavedOk` 决定是否刷新，并派发 `xf:dialog-closed`。

字段类型：`text | email | number | password | date | datetime | time | color | textarea | select | switch | checkbox | radio | hidden | static`。

## 8. 表格相关

| API | 签名 | 说明 |
|---|---|---|
| `table` | `table(idOrEl): DataTable\|null` | 取 `el.__xfTable` |
| `reloadTable` | `reloadTable(idOrEl, url?)` | 重载（可换 url） |
| `destroyTableSticky` | `destroyTableSticky(idOrEl)` | 清理固定列监听 |
| `viewRow` | `viewRow(row, cfg?, tableEl?)` | 多布局详情弹窗 |
| `editRow` | `editRow(row, cfg, tableEl?)` | 行编辑 |
| `bindRowDetail` | `bindRowDetail(table, el, cfg)` | 行明细 |
| `bindBulk` | `bindBulk(table, el, cfg)` | 批量操作 |
| `registerCellRenderer` | `registerCellRenderer(name, fn)` | 注册渲染器 |
| `getCellRenderer` | `getCellRenderer(name)` | 取渲染器 |
| `onCell` | `onCell(name, fn)` | 订阅单元格事件（链式返回 XFAdmin） |

`viewRow` 的 `cfg` 完整契约：

```js
XFAdmin.viewRow(row, {
    title: '客户详情 - {name}',
    size: 'lg',                    // sm | lg | xl
    layout: 'kv',                  // kv | profile | tabs | sections | template
    labels: { field: '中文名' },
    fields: ['a', 'b'],            // kv 白名单与顺序
    exclude: ['_internal'],
    cols: 2,
    header: { avatar: 'avatar', title: '{name}', sub: '{email}',
              badge: { field: 'status', map: { 1: { color: 'success', label: '正常' } } } },
    template: '<div>{name}</div>',
    sections: [
        { title: '基础', icon: 'ti ti-user', type: 'kv', fields: [...], cols: 2 },
        // type: kv | table | timeline | stats | tags | progress | images | html | template
    ],
    renderers: { amount: { type: 'money', prefix: '¥' } },
    ajax: '/api/users/{id}',       // 打开前拉取并合并
}, tableEl);
```

## 9. 交互增强

| API | 说明 |
|---|---|
| `bindXfPageLinks(root?)` | `[data-xf-page]` 点击 → `pageDialog`（`maximizable:true`） |
| `bindTwoFactor(root?)` | OTP 分格输入：自动跳格/退格/粘贴分发 |
| `bindQtyStepper(root?)` | 数量步进器 |
| `bindInvoicePrint(root?)` | 发票打印（仅打印 `.xf-invoice-print-area`） |
| `initCommandPalette(cfg)` | 命令面板（`hotkey: 'meta+k'`） |
| `onCommand(name, fn)` | 注册命令处理器 |
| `initDropzone(cfg)` | 拖拽上传区 |
| `onUpload(id, fn)` | 注册上传回调 |
| `i18n` | `{lang, dict, base, t(key), apply(root), set(code)}` |

## 10. data-* 属性契约

### 10.1 组件扫描

| 属性 | 语义 |
|---|---|
| `data-xf="<name>"` | 组件类型 |
| `data-xf-config='{...}'` | JSON 配置（必须合法 JSON） |

### 10.2 表格

| 属性 | 语义 |
|---|---|
| `data-xf-table="#id"` | 工具条/表单/弹窗 → 目标表格 |
| `data-field="2"` | `dt-filter` 目标列（纯数字=索引，否则 `name:name`） |
| `data-xf-filter-for="<tableId>"` | 过滤栏归属表格 |
| `data-xf-min` / `data-xf-max` | 双滑块隐藏值（`between` = `"lo,hi"`） |
| `data-xf-custom-value` | 自定义过滤组件的值 |
| `data-dt="<tableId>"` | 批量栏归属表格 |
| `data-xf-dataset` | 数据集标识 |
| `data-xf-bulk-action` + `data-url`/`data-method`/`data-confirm`/`data-action`/`data-reload` | 批量按钮 |

### 10.3 行操作

| 属性 | 语义 |
|---|---|
| `data-xf-act` | `edit`/`view`/`delete`/`ajax`/`copy-row`/`download`/`print`/`share`/自定义 |
| `data-xf-edit` | edit 的 JSON 配置 |
| `data-xf-view` | view 的 JSON 配置 |
| `data-xf-url` / `data-xf-method` | 请求地址与方法 |
| `data-xf-confirm` / `data-xf-confirm-popover="1"` | 确认 |
| `data-xf-reload="1"` | 成功后刷新 |
| `data-xf-event` | 存在时**不执行内置动作**，改派发自定义事件 |
| `data-xf-op` / `data-xf-dataset` / `data-xf-id` | 领域动作 |
| `data-xf-prompt` / `data-xf-arg` | 需要输入的动作（arg 默认 `comment`） |

### 10.4 单元格

| 属性 | 语义 |
|---|---|
| `data-xf-copy` | 点击复制 |
| `data-xf-qr` | 二维码原文，点击放大 |
| `data-xf-cell-event='{"click":"h1","dblclick":"h2"}'` | 单元格事件映射 |
| `data-xf-cell-row` | 行数据 JSON |
| `data-xf-field` / `data-xf-on` / `data-xf-off` | switch/input/toggle/select 提交字段与取值 |
| `data-url` / `data-field` | `select` 渲染器专用 |

### 10.5 弹窗与表单

| 属性 | 语义 |
|---|---|
| `data-xf-page-dialog="url"` | 点击弹窗加载页面（`data-xf-title`/`-size`/`-frame`/`-table`/`-reload`） |
| `data-xf-page="url"` | 同上，固定 `maximizable:true`、`reload:false` |
| `data-xf-maximizable="1"` | 显示最大化按钮 |
| `data-xf-page-content` | **服务端响应中**标记待提取的片段 |
| `data-xf-remote` | 表单 AJAX 托管 |
| `data-xf-reload="false"` | 成功后不刷新 |
| `data-xf-redirect` | 兜底跳转地址 |
| `data-xf-reset="1"` | 成功后重置表单 |
| `data-xf-autofocus` | 弹窗打开后优先聚焦 |
| `.xf-lbtn` + `.xf-lbtn-label` + `.xf-lbtn-spinner` | 忙碌按钮结构 |

### 10.6 认证页 / 验证码

| 属性 | 语义 |
|---|---|
| `data-xf-pin-input` / `data-xf-pin-cell` | 分格验证码 |
| `data-password="bar"` | 密码强度条容器 |
| `.password-input` + `.password-bar` | 另一套强度条（0–5 分） |
| `data-xf-captcha-refresh="<id>"` / `data-xf-captcha="<id>"` | 验证码图片与刷新 |
| `data-xf-captcha-slide` | 滑块验证码占位 |

### 10.7 其它

| 属性 | 语义 |
|---|---|
| `data-xf-count` / `data-xf-decimals` | 数字滚动目标与小数位 |
| `data-xf-animation` / `data-xf-trigger` | animate 动画名与触发方式 |
| `data-xf-update-url` / `data-xf-status-field` | 看板拖拽持久化端点与状态字段 |
| `data-min` / `data-max` / `data-step` | 数量步进器边界 |
| `data-toggle="fullscreen"` | 全屏切换（由 xfadmin.js 提供） |
| `data-action="card-close\|card-toggle\|card-refresh\|code-collapse"` | 卡片工具（app.js） |
| `data-provider="flatpickr\|timepickr"` | 日期时间选择器（app.js） |
| `data-touchspin` + `[data-minus]`/`[data-plus]` | 数字微调（app.js） |
| `data-xftable-search/-filter/-pagesize/-check-all/-delete/-pagination/-info` | `xftable` 挂点 |

## 11. 已注册 widget 全表

| `data-xf` | 依赖 | 说明 |
|---|---|---|
| `datatable` | DataTable | 数据表格（见 §12） |
| `dt-search` / `dt-filter` / `dt-pagesize` / `dt-views` | — | 工具条控件 |
| `xftable` | — | 静态交互表格（分页/搜索/筛选/全选/删除） |
| `apexchart` | ApexCharts | 通用图表 |
| `apextree` | ApexTree | 组织树 |
| `apexsankey` | ApexSankey | 桑基图 |
| `echart` | echarts | ECharts |
| `metric-chart` | echarts | 迷你图表（donut/pie/bar/line/area） |
| `vectormap` | jsVectorMap | 矢量地图 |
| `choices` / `select2` | Choices / jQuery+Select2 | 增强下拉 |
| `daterangepicker` | jQuery+moment | 日期范围（内置中文） |
| `slider` | noUiSlider | 范围滑块 |
| `quill` / `summernote` | Quill / jQuery+Summernote | 富文本 |
| `dropzone` / `filepond` | Dropzone / FilePond | 上传 |
| `pickr` | Pickr | 颜色选择 |
| `inputmask` | Inputmask | 输入掩码 |
| `tagify` | Tagify | 标签输入 |
| `sortable` / `nestable` | Sortable | 拖拽排序 / 嵌套排序 |
| `jstree` | jQuery+jsTree | 树形控件 |
| `lightbox` | GLightbox | 灯箱 |
| `masonry` | Masonry | 瀑布流 |
| `calendar` | FullCalendar | 日历（内置中文） |
| `tour` | tourguide | 新手引导 |
| `clipboard` | ClipboardJS | 复制 |
| `sweetalert` | Swal | 弹窗 |
| `ladda` | Ladda | 按钮加载动效 |
| `counter` | — | 数字滚动 |
| `countup` | — | 数字递增（返回 `{destroy}`） |
| `form` | — | 遗留：原生校验 + AJAX（新代码用 `data-xf-remote`） |
| `wizard` | — | 分步向导 |
| `kanban` | Sortable（可选） | 看板 |
| `email` | — | 邮件列表搜索过滤 |
| `chat-scroll` / `chat-form` | — | 聊天滚动与发送 |
| `issues` | — | 问题列表搜索 |
| `votelist` | — | 投票 |
| `todo` | — | 待办 |
| `animate` | animate.css | 入场动画 |
| `countdown` | — | 倒计时（返回 `{destroy}`） |
| `backtotop` | — | 回到顶部 |
| `codeCopy` | — | 代码块复制 |
| `twoFactor` | — | OTP（由 `bindTwoFactor` 绑定） |
| `qtyStepper` | — | 数量步进（由 `bindQtyStepper`） |
| `print` | — | 打印（由 `bindInvoicePrint`） |

## 12. 事件全表

| 事件 | 目标 | 时机 | `detail` |
|---|---|---|---|
| `xf:action` | document | 点击任意 `[data-xf-act]` | `{action, row, el, table}` |
| `xf:toggle` | document | 点 `.xf-cell-toggle` | `{el, value, field, id, row}` |
| `xf:switch` | document | `.xf-cell-switch` change | `{el, checked, value, field, id, row}` |
| `xf:cell-input` | document | `.xf-cell-input` change | `{el, value, field, id, row}` |
| `xf:cell-select` | document | `.xf-cell-select` change | `{el, value, field, row}` |
| `xf:edit-save` | document | 表单弹窗提交 | `{row, data, table}` |
| `xf:dialog-closed` | document | 弹窗关闭 | `{url, reloaded, tableId, saved}` |
| `xf:filter-custom` | document（不冒泡） | 收集自定义过滤值 | `{name, el, getValue(v)}` —— **必须调用 getValue 回传** |
| `xf:custom-change` | 自定义过滤元素 | 值变更（宿主派发） | 自定义 |
| `xf:lang-changed` | document | 语言切换 | `{lang}` |
| `xf.dtfilter.change` | 筛选控件（冒泡） | `dt-filter` change | `{value, field, api}` |
| `xf.dtview.change` | 视图容器（冒泡） | `dt-views` 点击 | `{view, button, api}` |
| `xf.form.success` / `xf.form.error` | form（冒泡） | AJAX 提交完成 | `{ok, status, data}` / Error |
| `xf.wizard.change` | wizard 容器 | 步骤切换 | `{step}` |
| `xf.wizard.finish` | wizard 容器（冒泡） | 末步提交 | `{step}` |
| `xf.kanban.move` | 看板容器（冒泡） | 卡片移动 | `{item, from, to, fromIndex, toIndex, card}` |
| `xf.kanban.add` | 看板容器（冒泡） | 新增卡片 | `{column}` |
| `xf.chat.send` | 聊天表单（冒泡） | 发送消息 | `{text}` |
| `xf.chip.close` | badge（冒泡） | 关闭 chip | — |
| `xf.swal.closed` | sweetalert 宿主 | Swal 关闭 | result |

```js
document.addEventListener('xf:action', e => console.log(e.detail.action, e.detail.row));
document.addEventListener('xf:dialog-closed', e => {
    if (e.detail.saved) XFAdmin.reloadTable(e.detail.tableId);
});
document.addEventListener('xf:filter-custom', e => e.detail.getValue(myWidget.value));
XFAdmin.onCell('onNameClick', ({ event, row, el, value, field }) => { /* … */ });
```

> 注：**不存在** `xf:ready` 事件，只有 `XFAdmin.onReady(fn)` 回调。

## 13. 主题（config.js / app.js）

```js
// 持久化键：sessionStorage['__INSPINIA_CONFIG__']
window.defaultConfig = {
    skin: 'classic',                  // data-skin
    theme: 'light',                   // data-bs-theme（'system' 跟随系统）
    layout: { position: 'fixed' },    // data-layout-position
    topbar: { color: 'light' },       // data-topbar-color
    menu: { color: 'dark' },          // data-menu-color
    sidenav: { size: 'default', user: true },
};
```

- 启动时把配置写回 `<html>` 的 7 个 data 属性（防首屏闪烁）；
- 响应式强制：≤767px → `offcanvas`；≤1140px 且非 offcanvas → `condensed`；
- `xfadmin.js` 在文件最前面同步 `localStorage.setItem('__user_has_visited__','true')`，
  阻止 `app.js` 首次访问自动弹出主题定制面板。

`app.js` 主要职责：`lucide.createIcons()`、卡片工具（`data-action`）、
侧边栏菜单（互斥展开 + 自动高亮 + 滚动定位）、`LayoutCustomizer`（主题面板）、
`Plugins.initFlatPicker` / `initTouchSpin`、`I18nManager`、
`CustomApexChart` / `CustomEChart` 实例池与主题重绘。

## 14. i18n

```js
XFAdmin.i18n.set('en');      // 切换语言（localStorage['xfadmin.lang']）
XFAdmin.i18n.t('key');
XFAdmin.i18n.set('');        // 恢复原文
```

- 翻译文件目录默认由 `script[src*=xfadmin.js]` 推导为 `../data/translations/`；
- `[data-lang]` 元素首次翻译前备份到 `data-xf-i18n-orig`；
- 切换成功派发 `xf:lang-changed`。

## 15. 调试技巧

```js
// 查看已注册 widget
Object.keys(XFAdmin.widgets);

// 查看某元素是否已初始化
document.querySelector('#x').__xfInited;

// 手动重扫
XFAdmin.scan(document.body);

// 取表格实例
XFAdmin.table('#user-table').ajax.reload();

// 查看单元格渲染器
Object.keys(XFAdmin.cellRenderers);
```
