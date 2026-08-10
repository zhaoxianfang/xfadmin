# 页面模板（Layout / Templates）

> 页面模板会输出**完整的 HTML 文档**（含 `<!DOCTYPE>`、`<head>`、主题样式与脚本），通常用于独立路由渲染，而非嵌套在其它页面内。完整选项见 [组件详细参考](../components-reference.md)。


## `page`

整页骨架（完整 HTML 文档）

```php
XfAdmin::page([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#page](../components-reference.md#page)

## `authPage`

认证页骨架（登录 / 注册 / 找回密码 / 锁屏等）

```php
XfAdmin::authPage([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#authPage](../components-reference.md#authPage)

## `lockScreen`

锁屏页（auth-lock-screen.html）—— 全屏锁定，输入密码解锁

```php
XfAdmin::lockScreen([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#lockScreen](../components-reference.md#lockScreen)

## `errorPage`

错误页（404 / 500 / 503 …）

```php
XfAdmin::errorPage([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#errorPage](../components-reference.md#errorPage)

## `comingSoon`

即将上线页（pages-coming-soon.html）—— 含倒计时

```php
XfAdmin::comingSoon([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#comingSoon](../components-reference.md#comingSoon)

## `maintenance`

维护中页（maintenance.html）

```php
XfAdmin::maintenance([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#maintenance](../components-reference.md#maintenance)

## `emptyState`

空状态占位（pages-empty.html / pages-search-results.html 无结果）

```php
XfAdmin::emptyState([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#emptyState](../components-reference.md#emptyState)

## `landing`

营销落地页（landing.html）—— 返回完整独立页面（Page）

```php
XfAdmin::landing([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#landing](../components-reference.md#landing)

## `profilePage`

个人主页（封面 + 头像 + 统计 + 操作 + 标签页）—— INSPINIA pages-profile.html 整页抽取

```php
XfAdmin::profilePage([ /* 见组件详细参考 */ ]);
```

> 详细选项 / 示例：[组件详细参考#profilePage](../components-reference.md#profilePage)
