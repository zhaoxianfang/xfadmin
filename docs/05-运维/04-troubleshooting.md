# 问题排查

按「现象 → 原因 → 处理」组织，覆盖全部已知陷阱。

## 1. 页面无样式 / 无交互

| 可能原因 | 检查 |
|---|---|
| 未输出 `head()` / `scripts()` | 用 `XfAdmin::page()` 会自动输出；自定义布局需手工输出 |
| 资源 404 | 浏览器 F12 → Network，看 `/zxf/xfadmin/...` 是否 404 |
| 未发布资源（ThinkPHP） | 执行 `php think xfadmin:publish` |
| `assets_url` 配置错误 | 与发布目录一致 |
| 组件在 `head()` 之后渲染 | 先渲染内容为字符串，再输出 head |
| Web 服务器未放行目录 | Nginx 配置检查 |

## 2. 组件渲染异常

| 现象 | 原因 | 处理 |
|---|---|---|
| `InvalidArgumentException: 未知组件 [xxx]` | 别名拼错 | 见 [组件索引](../06-附录/01-component-index.md)；别名大小写不敏感但需存在 |
| 输出空字符串 | 必需参数缺失（如 `items` 为空） | 检查参数 |
| `Object of class Closure could not be converted to string` | 内容槽位传了未调用的闭包 | 组件会自动调用，检查是否自己拼接了闭包 |
| 组件重复渲染 | 同一实例被 echo 多次（每次生成新 uid） | 需要复用请先 `(string)` 一次 |

## 3. 配置不生效

| 现象 | 原因 | 处理 |
|---|---|---|
| 传了参数没变化 | 键名拼错（组件忽略未知键） | 对照组件文档的参数表 |
| 数组参数"多出重复项" | `array_replace_recursive` 按索引并集合并 | 设计组件时列表型默认留 `[]`；调用方传完整数组 |
| 表单字段默认值不生效 | 子类 `+` 合并时同名键被 `fieldDefaults()` 覆盖 | 不要在子类 defaults 里重定义公共键 |
| 主题不生效 | `theme` 值非法被回退 | 检查枚举值 |
| 修改全局配置无效果 | 配置缓存 | Laravel `config:clear`；ThinkPHP `php think clear` |

## 4. 表格问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 表格空白 / 一直加载 | `window.DataTable` 未加载；或初始化异常被静默 | F12 看 console；确认资源加载 |
| "Requested unknown parameter" | 列 `data` 为空且无 `defaultContent` | 组件已自动补，若自定义 `options.columns` 需自行补 |
| 行操作按钮点两次 / prompt 弹两次 | 全局委托与行委托重复 | 已有 `btn.closest('table')` 守卫；自定义按钮避免在表格内用 `data-xf-op` + `data-xf-act` 双写 |
| 「更多」下拉不弹出 / 飞出视口 | DataTables scrollX + 固定列导致 Popper 定位偏移 | `initDtDropdownFix` 已在 `shown.bs.dropdown` 的 rAF 内改为 fixed 定位 |
| 过滤栏不生效 | 前端控件名与后端 `filters` 键不对应 | 对照 [data-protocol §11](../02-核心架构/05-data-protocol.md#11-完整示例) |
| 服务端返回 302 登录页 | 接口未走 web 中间件 / 未登录 | 检查路由中间件 |
| 服务端 419 | CSRF | 确认 meta csrf-token；用 `method => 'POST'` |
| 排序错列 | 启用 bulk/row_detail 后索引被辅助列顶偏 | 用列名或调整索引 |
| 导出无 PDF | 未加载 pdfmake | `export` 含 `pdf` 时自动加载（写在 `options.buttons` 里检测不到） |
| `row_group` 无效 | 服务端模式下无效（已分页） | 仅本地数据可用 |
| 固定列错位 | 列宽同步时机 | 组件已内置多道校正；自定义容器需保证有宽度 |
| `Cannot extend unknown button type` | 用了 `printHtml5` | 用 `print` |

## 5. 表单问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 提交后 419 | 无 CSRF token | `'csrf' => true`（Form 默认 `[]` 不注入） |
| AJAX 提交后不跳转 | 后端未返回顶层 `url` | 返回 `['ok'=>true,'url'=>'/x']` |
| 字段错误不显示 | 后端未返回 422 + `errors` | Laravel 自动；其它框架手工返回 |
| `select` 选中不生效 | 严格字符串比较（`0` ≠ `''`） | 统一类型 |
| `placeholder` 不显示 | 多选模式下不生效（设计如此） | 单选才支持 |
| `mask` 与 `tags` 同时无效 | `mask` 优先，二者互斥 | 只用一个 |
| `maskedInput` 的 `alias` 无效 | 前端只消费 `mask` | 用 `mask` 或自行扩展 |
| quill 初始内容被转义/丢失 | quill 模式 value 是 `raw()` 输出 | 保证 HTML 可信 |
| 颜色选择器点了没保存 | 未点 Pickr 的 save 按钮 | 点击 save 才写回隐藏 input |
| 向导无法进入下一步 | 当前面板校验未通过 | `reportValidity()` 会提示具体字段 |

## 6. 认证页问题

| 现象 | 原因 | 处理 |
|---|---|---|
| `XfAdmin::lockScreen()` 参数无效 | 得到的是独立 `LockScreen` 组件 | 用 `authPage(['type'=>'lock-screen'])` |
| 表单 method 不是 PUT | `<form>` 恒输出 `method="POST"` | 用 `_method` 隐藏域 |
| 验证码不显示 | `captcha => true` 只输出占位 | 传字符串（组件或第三方视图） |
| 字段没渲染 | `fields` 必须是关联数组 | `['email' => [...]]` |
| 提交后无响应 | 未开 `ajax` 且 action 为空 | 设 `action` 或 `ajax => true` |

## 7. 前端 JS 问题

| 现象 | 原因 | 处理 |
|---|---|---|
| `XFAdmin is not defined` | 未输出 `scripts()` | 检查 |
| 组件不初始化 | `data-xf-config` 不是合法 JSON | 检查 JSON（PHP 侧已用 `JSON_HEX_*`） |
| 依赖缺失警告 | 缺 jQuery 等 | 确认插件资源加载；只警告一次不阻断 |
| `XFAdmin.request` 参数没发送 | 传了 `opts.body` | 用 `opts.data` |
| 单元格事件不触发 | 未在 document 上监听 / 渲染器未配 `event` | 用 `XFAdmin.onCell` 或正确配置 |
| `xf:filter-custom` 收不到 | 事件 `bubbles:false` 且派发到 document | 必须在 `document` 上直接监听 |
| 切换主题图表不变 | 未加载 `app.js` 或 MutationObserver 未生效 | 确认 `scripts()` |
| 打印整页而非指定区域 | 未加 `body.xf-printing` | 用 `bindInvoicePrint` 的按钮 |

## 8. 样式问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 顶栏右移 235px | 框架给 `.app-topbar` 设了 `margin-left` | `xfadmin.css` 已重置；自定义布局需自行加 `margin:0` |
| 页面横向出血 | `.page-title-head` 框架 `margin: 0 -1.25rem` | 已重置；自定义时注意 |
| 时间线横向了 | 框架 `.timeline-content` 是 `position:absolute` | 已重置 |
| 图片被拉高 | 未设 `object-fit` / 高度 | 用 `carousel`/`productCard` 内置类；或自加 CSS |
| 暗色模式部分不生效 | 组件内硬编码颜色 | 改用 CSS 变量 / `text-bg-*` |
| 表格双重滚动条 | `.table-responsive` 与 scrollX 同时存在 | 组件已互斥，检查手工包裹 |

## 9. 数据协议问题

| 现象 | 原因 | 处理 |
|---|---|---|
| 过滤无效果 | 前端名与 `filters` 键不匹配 | 对照表 |
| 排序报未知列 | Builder 直接用请求列名 | 自行加白名单 |
| 计数不对 | `transform` 在分页后执行 | 计数不受 transform 影响（设计如此） |
| `length=-1` 返回太多 | 被夹到 1000 | `DataSet::$maxLength` 可调 |
| 数组管线 `in` 匹配失败 | 严格字符串比较 | 统一类型 |
| 闭包过滤无效 | 两种管线签名不同 | 数组 `fn($row,$v):bool`；Builder `fn($query,$v):void` |

## 10. 框架集成问题

| 现象 | 原因 | 处理 |
|---|---|---|
| Laravel Facade 找不到 | 别名未注册 | `composer dump-autoload`，或用完整类名 |
| `route:cache` 失败 | 自己注册了闭包路由 | 本包用控制器方法，兼容 |
| ThinkPHP 资源全 404 | 未发布 | `php think xfadmin:publish` |
| ThinkPHP 服务端表格报错 | 查询构造器方法不兼容 | 转数组后传给 `dataResponse` |
| 改配置不生效 | 缓存 | 清配置缓存 |

## 11. 调试技巧

```php
// 1. 确认组件注册
var_dump(XfAdmin::has('dataTable'), XfAdmin::componentList()['card']);

// 2. 查看组件合并后的配置
print_r(XfAdmin::card(['title' => 'x'])->options());

// 3. 查看资源收集
print_r(XfAdmin::assets()->cssFiles());
print_r(XfAdmin::assets()->jsFiles());

// 4. 渲染单组件看输出
echo XfAdmin::card(['title' => 'x', 'body' => 'y']);
```

```js
// 5. 前端：已注册 widget
Object.keys(XFAdmin.widgets);

// 6. 元素是否已初始化
document.querySelector('#x').__xfInited;

// 7. 手动重扫
XFAdmin.scan(document.body);

// 8. 取表格实例
XFAdmin.table('#user-table').ajax.reload();
```

## 12. 仍未解决？

1. 运行 `bash tests/run-all.sh` 确认包本身健康；
2. 运行 `bash tools/selftest/run.sh` 做浏览器级自检；
3. 检查浏览器控制台与 Network 面板；
4. 在 [FAQ](../06-附录/04-faq.md) 与 [组件索引](../06-附录/01-component-index.md) 中检索；
5. 提交 issue 时附上：PHP 版本、框架版本、组件别名、完整配置数组、渲染输出片段、控制台错误。
