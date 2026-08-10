/*!
 * XfAdmin 前端运行时（纯原生 JS，无 Node.js 依赖）
 *
 * 职责：
 *  1. 自动扫描 [data-xf] 元素并按 data-xf-config 初始化对应插件
 *  2. 动态按需加载 CSS/JS（去重：同一资源全局最多加载一次）
 *  3. 对 AJAX 注入的新内容可调用 XFAdmin.scan(root) 增量初始化
 *  4. 提供 XFAdmin.toast / XFAdmin.confirm 等便捷交互 API
 */
(function (global) {
    'use strict';

    var XFAdmin = {
        version: '1.0.0',
        widgets: {},
        instances: new WeakMap(),
        _loaded: {},
        _readyQueue: []
    };

    /* 禁止框架（app.js）在"首次访问"时自动弹出主题定制面板：
     * openCustomizer 于 DOMContentLoaded 检查 __user_has_visited__，
     * 本脚本在其之前同步执行，预置标记即可避免遮罩覆盖全页打断用户。 */
    try { localStorage.setItem('__user_has_visited__', 'true'); } catch (e) { /* 隐私模式下忽略 */ }

    /* 注册 DOM 就绪后执行的回调（重复注册 / 已就绪均安全） */
    XFAdmin.onReady = function (fn) {
        if (typeof fn !== 'function') return;
        if (document.readyState !== 'loading') {
            try { fn(); } catch (e) { console.error('[XFAdmin] onReady', e); }
        } else {
            XFAdmin._readyQueue.push(fn);
        }
    };

    /* ------------------------------------------------------------------
     * 资源动态加载（去重）
     * ---------------------------------------------------------------- */
    function absUrl(url) {
        var a = document.createElement('a');
        a.href = url;
        return a.href;
    }

    XFAdmin.loadStyle = function (href) {
        var key = absUrl(href);
        if (XFAdmin._loaded[key]) return XFAdmin._loaded[key];
        // 检查页面已有的 link
        var exists = Array.prototype.some.call(document.querySelectorAll('link[rel="stylesheet"]'), function (l) {
            return l.href === key;
        });
        if (exists) return (XFAdmin._loaded[key] = Promise.resolve());
        XFAdmin._loaded[key] = new Promise(function (resolve, reject) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.onload = resolve;
            link.onerror = reject;
            document.head.appendChild(link);
        });
        return XFAdmin._loaded[key];
    };

    XFAdmin.loadScript = function (src) {
        var key = absUrl(src);
        if (XFAdmin._loaded[key]) return XFAdmin._loaded[key];
        var exists = Array.prototype.some.call(document.querySelectorAll('script[src]'), function (s) {
            return s.src === key;
        });
        if (exists) return (XFAdmin._loaded[key] = Promise.resolve());
        XFAdmin._loaded[key] = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
        return XFAdmin._loaded[key];
    };

    /** 顺序加载一组 JS + 并行加载一组 CSS */
    XFAdmin.load = function (css, js) {
        var cssP = (css || []).map(XFAdmin.loadStyle);
        var jsP = (js || []).reduce(function (chain, src) {
            return chain.then(function () { return XFAdmin.loadScript(src); });
        }, Promise.resolve());
        return Promise.all(cssP.concat([jsP]));
    };

    /* ------------------------------------------------------------------
     * 组件扫描与初始化
     * ---------------------------------------------------------------- */
    XFAdmin.register = function (name, initFn) {
        XFAdmin.widgets[name] = initFn;
    };

    function readConfig(el) {
        var raw = el.getAttribute('data-xf-config');
        if (!raw) return {};
        try { return JSON.parse(raw); } catch (e) {
            console.warn('[XfAdmin] data-xf-config JSON 解析失败', el, e);
            return {};
        }
    }

    /* 转义用户数据用于 HTML 上下文，防止存储型 XSS（DataTable 模板列 / 徽章列） */
    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    XFAdmin.escapeHtml = escapeHtml;

    /* {field} / {a.b.c} 占位符插值（值经 HTML 转义） */
    XFAdmin.tpl = function (tpl, row) {
        return String(tpl == null ? '' : tpl).replace(/\{(\w+(?:\.\w+)*)\}/g, function (_, path) {
            var v = path.split('.').reduce(function (o, k) { return o == null ? '' : o[k]; }, row || {});
            return escapeHtml(v == null ? '' : v);
        });
    };

    /* {field} 占位符插值（原样输出，不做 HTML 转义；用于 HTML 属性拼接前统一 escapeHtml 一次，避免双重转义） */
    function tplRaw(tpl, row) {
        return String(tpl == null ? '' : tpl).replace(/\{(\w+(?:\.\w+)*)\}/g, function (_, path) {
            var v = path.split('.').reduce(function (o, k) { return o == null ? '' : o[k]; }, row || {});
            return v == null ? '' : String(v);
        });
    }
    XFAdmin.tplRaw = tplRaw;

    /* 读取 CSRF Token（Laravel <meta name="csrf-token">，可选） */
    XFAdmin.csrf = function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    /**
     * 统一 AJAX 请求（自动携带 CSRF / JSON 解析 / 错误 toast）
     * XFAdmin.request('/api/xx', { method: 'POST', data: {...}, silent: false })
     *   => Promise<{ok, status, data}>
     */
    XFAdmin.request = function (url, opts) {
        opts = opts || {};
        var method = (opts.method || 'GET').toUpperCase();
        var headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }, opts.headers || {});
        var init = { method: method, headers: headers, credentials: 'same-origin' };
        if (method !== 'GET' && method !== 'HEAD') {
            var token = XFAdmin.csrf();
            if (token) headers['X-CSRF-TOKEN'] = token;
            if (opts.data instanceof FormData) {
                init.body = opts.data;
            } else if (opts.data != null) {
                headers['Content-Type'] = 'application/json;charset=utf-8';
                init.body = JSON.stringify(opts.data);
            }
        }
        var ctrl;
        if (typeof AbortController !== 'undefined') {
            ctrl = new AbortController();
            init.signal = ctrl.signal;
            var timeout = parseInt(opts.timeout, 10);
            if (timeout > 0) setTimeout(function () { ctrl.abort(); }, timeout);
        }
        // 解析响应：opts.raw 时直接返回文本；非 JSON 自动退化为 {message}
        var parse = function (res) {
            if (opts.raw) return res.text().then(function (t) { return { __raw: t }; });
            return res.json().catch(function () { return res.text().then(function (t) { return { __raw: t }; }); });
        };
        return fetch(url, init).then(function (res) {
            return parse(res).then(function (data) {
                if (data && data.__raw !== undefined) {
                    if (opts.raw) return { ok: res.ok, status: res.status, data: { __raw: data.__raw }, raw: data.__raw };
                    data = { message: res.statusText || ('请求失败(' + res.status + ')') };
                }
                var result = {
                    ok: res.ok, status: res.status,
                    data: data,
                    errors: (data && data.errors) || null
                };
                if (!res.ok && !opts.silent) {
                    var msg = (data && (data.message || data.msg)) || ('请求失败(' + res.status + ')');
                    XFAdmin.toast({ body: msg, variant: 'danger' });
                }
                return result;
            });
        }).catch(function (err) {
            if (err && err.name === 'AbortError') {
                if (!opts.silent) XFAdmin.toast({ body: '请求超时，请稍后重试', variant: 'danger' });
                return { ok: false, status: 408, data: { message: '请求超时' }, error: err };
            }
            if (!opts.silent) XFAdmin.toast({ body: '网络错误，请稍后重试', variant: 'danger' });
            return { ok: false, status: 0, data: {}, error: err };
        });
    };

    /* 复制到剪贴板（navigator.clipboard 优先，textarea 兜底） */
    XFAdmin.copyText = function (text, tip) {
        function done() { XFAdmin.toast({ body: tip || '已复制！', variant: 'success', delay: 1500 }); }
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).then(done).catch(function () { fallback(); });
        }
        fallback();
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;opacity:0;';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); done(); } catch (e) { /* noop */ }
            ta.remove();
        }
    };

    /* DataTables 中文语言包（缺省内置，可被配置覆盖） */
    XFAdmin.dtLanguage = {
        processing: '处理中...',
        lengthMenu: '每页 _MENU_ 条',
        zeroRecords: '没有匹配的记录',
        info: '第 _START_ 至 _END_ 条，共 _TOTAL_ 条',
        infoEmpty: '共 0 条记录',
        infoFiltered: '（由 _MAX_ 条记录过滤）',
        search: '搜索:',
        emptyTable: '表中数据为空',
        loadingRecords: '载入中...',
        // 分页按钮与 INSPINIA 模板一致，使用 Tabler 箭头图标（模板全部 DataTable 均如此）；
        // 保留 title 以兼顾可访问性与中文语义。
        paginate: {
            first: '<i class="ti ti-chevrons-left" title="首页"></i>',
            previous: '<i class="ti ti-chevron-left" title="上一页"></i>',
            next: '<i class="ti ti-chevron-right" title="下一页"></i>',
            last: '<i class="ti ti-chevrons-right" title="末页"></i>'
        },
        aria: { sortAscending: ': 升序排列', sortDescending: ': 降序排列' },
        select: { rows: { _: '已选择 %d 行', 0: '', 1: '已选择 1 行' } },
        buttons: { copy: '复制', csv: 'CSV', excel: 'Excel', print: '打印', pdf: 'PDF', colvis: '列显示', copyTitle: '已复制到剪贴板', copySuccess: { _: '已复制 %d 行', 1: '已复制 1 行' } }
    };

    /* ------------------------------------------------------------------
     * DataTable 富单元格渲染器（可通过 XFAdmin.cellRenderers.xxx 扩展）
     * 每个渲染器：function (data, row, cfg, meta) => html 字符串
     * ---------------------------------------------------------------- */
    function str(v) { return v == null ? '' : String(v); }

    // 二维码库：统一使用 UTF-8 字节编码，支持中文等非 ASCII 内容
    if (typeof qrcode === 'function' && qrcode.stringToBytesFuncs && qrcode.stringToBytesFuncs['UTF-8']) {
        qrcode.stringToBytes = qrcode.stringToBytesFuncs['UTF-8'];
    }

    XFAdmin.cellRenderers = {
        /* 纯文本（转义） */
        text: function (d) { return escapeHtml(d); },

        /* 单元格输入框：cfg = {size, url(可选，change 时 PATCH 提交), placeholder} */
        input: function (d, row, cfg) {
            return '<input type="text" class="form-control form-control-' + (cfg.size || 'sm') + ' xf-cell-input" value="' + escapeHtml(d) +
                '"' + (cfg.placeholder ? ' placeholder="' + escapeHtml(cfg.placeholder) + '"' : '') +
                (cfg.url ? ' data-xf-url="' + escapeHtml(XFAdmin.tpl(cfg.url, row)) + '"' : '') +
                ' data-xf-field="' + escapeHtml(cfg.field || '') + '" data-xf-id="' + escapeHtml(row && (row.id != null ? row.id : '') || '') + '">';
        },

        /* 带复制按钮的输入框 */
        copy: function (d, row, cfg) {
            var v = escapeHtml(d);
            return '<div class="input-group input-group-sm flex-nowrap xf-cell-copy" style="min-width:120px">' +
                '<input type="text" class="form-control" value="' + v + '" readonly>' +
                '<button type="button" class="btn btn-soft-secondary" data-xf-copy="' + v + '" title="复制"><i class="ti ti-copy"></i></button></div>';
        },

        /* IP 地址：等宽字体 + 点击复制 */
        ip: function (d) {
            var v = escapeHtml(d);
            if (!v) return '<span class="text-muted">-</span>';
            return '<span class="font-monospace badge bg-light text-dark border xf-pointer" data-xf-copy="' + v + '" title="点击复制">' + v + '</span>';
        },

        /* 状态开关：cfg = {url({id} 占位), field, on: 1, off: 0} */
        switch: function (d, row, cfg) {
            var on = cfg.on !== undefined ? cfg.on : 1;
            var checked = String(d) === String(on) || d === true;
            return '<div class="form-check form-switch d-inline-block mb-0">' +
                '<input type="checkbox" class="form-check-input xf-cell-switch" role="switch"' + (checked ? ' checked' : '') +
                (cfg.url ? ' data-xf-url="' + escapeHtml(XFAdmin.tpl(cfg.url, row)) + '"' : '') +
                ' data-xf-field="' + escapeHtml(cfg.field || '') + '" data-xf-on="' + escapeHtml(str(on)) + '" data-xf-off="' + escapeHtml(str(cfg.off !== undefined ? cfg.off : 0)) +
                '" data-xf-id="' + escapeHtml(row && (row.id != null ? row.id : '') || '') + '"></div>';
        },

        /* 标签组：值为数组或逗号分隔字符串；cfg = {variant / variants: [...轮换色]} */
        tags: function (d, row, cfg) {
            var arr = Array.isArray(d) ? d : str(d).split(/[,，;；]/);
            var palette = cfg.variants || ['primary', 'success', 'info', 'warning', 'secondary'];
            var html = '';
            arr.forEach(function (t, i) {
                t = String(t).trim();
                if (!t) return;
                var v = cfg.variant || palette[i % palette.length];
                html += '<span class="badge bg-' + v + '-subtle text-' + v + ' me-1">' + escapeHtml(t) + '</span>';
            });
            return html || '<span class="text-muted">-</span>';
        },

        /* 颜色块：值为 #hex / rgb() */
        color: function (d) {
            var v = escapeHtml(d);
            if (!v) return '<span class="text-muted">-</span>';
            return '<span class="d-inline-flex align-items-center gap-1 xf-pointer" data-xf-copy="' + v + '" title="点击复制">' +
                '<span class="d-inline-block rounded border" style="width:1.2em;height:1.2em;background:' + v + '"></span>' +
                '<code>' + v + '</code></span>';
        },

        /* 图片：cfg = {height, rounded, circle} */
        image: function (d, row, cfg) {
            var v = escapeHtml(d);
            if (!v) return '<span class="text-muted">-</span>';
            var cls = cfg.circle ? 'rounded-circle' : (cfg.rounded === false ? '' : 'rounded');
            return '<img src="' + v + '" class="' + cls + '" style="height:' + (cfg.height || 32) + 'px" loading="lazy" alt="">';
        },

        /* 头像 + 名称：cfg = {name_field} */
        avatar: function (d, row, cfg) {
            var name = escapeHtml(cfg.name_field ? row[cfg.name_field] : '');
            var img = d ? '<img src="' + escapeHtml(d) + '" class="rounded-circle me-1" style="width:28px;height:28px;object-fit:cover" alt="">'
                : '<span class="avatar-xs bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-1" style="width:28px;height:28px">' + escapeHtml(String(name || '?').charAt(0).toUpperCase()) + '</span>';
            return '<span class="d-inline-flex align-items-center">' + img + name + '</span>';
        },

        /* 进度条：cfg = {variant, striped, max} */
        progress: function (d, row, cfg) {
            var pct = Math.max(0, Math.min(100, Math.round((parseFloat(d) || 0) / (cfg.max || 100) * 100)));
            var variant = cfg.variant || (pct >= 80 ? 'success' : pct >= 40 ? 'info' : 'warning');
            return '<div class="d-flex align-items-center gap-2" style="min-width:100px">' +
                '<div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-' + variant + (cfg.striped ? ' progress-bar-striped' : '') + '" style="width:' + pct + '%"></div></div>' +
                '<small class="text-muted">' + pct + '%</small></div>';
        },

        /* 布尔：√ / × 图标 */
        bool: function (d) {
            var truthy = d === true || d === 1 || d === '1' || d === 'true' || d === 'yes' || d === 'on';
            return truthy ? '<i class="ti ti-circle-check-filled text-success fs-5"></i>'
                : '<i class="ti ti-circle-x-filled text-danger fs-5"></i>';
        },

        /* 链接：cfg = {href({占位}), target, text} */
        link: function (d, row, cfg) {
            var href = cfg.href ? XFAdmin.tpl(cfg.href, row) : escapeHtml(d);
            var text = cfg.text ? XFAdmin.tpl(cfg.text, row) : escapeHtml(d);
            if (!href) return '<span class="text-muted">-</span>';
            return '<a href="' + href + '"' + (cfg.target ? ' target="' + escapeHtml(cfg.target) + '"' : '') + ' class="link-primary">' + text + '</a>';
        },

        /* 代码片段 */
        code: function (d) {
            var v = escapeHtml(d);
            return v ? '<code class="xf-pointer" data-xf-copy="' + v + '" title="点击复制">' + v + '</code>' : '<span class="text-muted">-</span>';
        },

        /* 日期时间：cfg = {ago: true 显示相对时间} */
        datetime: function (d, row, cfg) {
            var v = str(d);
            if (!v) return '<span class="text-muted">-</span>';
            if (cfg.ago) {
                var t = new Date(v.replace(/-/g, '/')).getTime();
                if (!isNaN(t)) {
                    var diff = Math.floor((Date.now() - t) / 1000);
                    var ago = diff < 60 ? '刚刚' : diff < 3600 ? Math.floor(diff / 60) + ' 分钟前'
                        : diff < 86400 ? Math.floor(diff / 3600) + ' 小时前'
                        : diff < 2592000 ? Math.floor(diff / 86400) + ' 天前' : v.slice(0, 10);
                    return '<span title="' + escapeHtml(v) + '">' + escapeHtml(ago) + '</span>';
                }
            }
            return '<span class="text-nowrap">' + escapeHtml(v) + '</span>';
        },

        /* 金额：cfg = {prefix: '¥', decimals: 2} */
        money: function (d, row, cfg) {
            var n = parseFloat(d) || 0;
            var s = n.toFixed(cfg.decimals !== undefined ? cfg.decimals : 2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return '<span class="font-monospace' + (n < 0 ? ' text-danger' : '') + '">' + escapeHtml(cfg.prefix !== undefined ? cfg.prefix : '¥') + s + '</span>';
        },

        /* 截断长文本：cfg = {length: 30} */
        truncate: function (d, row, cfg) {
            var v = str(d);
            var len = cfg.length || 30;
            if (v.length <= len) return escapeHtml(v);
            return '<span title="' + escapeHtml(v) + '">' + escapeHtml(v.slice(0, len)) + '…</span>';
        },

        /* 星级评分：cfg = {max: 5} */
        rating: function (d, row, cfg) {
            var max = cfg.max || 5;
            var n = Math.max(0, Math.min(max, parseFloat(d) || 0));
            var html = '<span class="text-warning text-nowrap">';
            for (var i = 1; i <= max; i++) {
                html += '<i class="ti ti-star' + (i <= n ? '-filled' : (i - n < 1 ? '-half-filled' : '')) + '"></i>';
            }
            return html + '</span>';
        },

        /* 图标：值即图标类名；或 cfg = {map: {value: 'ti ti-xxx text-success'}} 映射 */
        icon: function (d, row, cfg) {
            if (cfg.map) {
                var cls = cfg.map[d];
                return cls ? '<i class="' + escapeHtml(cls) + ' fs-5"></i>' : escapeHtml(d);
            }
            return d ? '<i class="' + escapeHtml(d) + ' fs-4" title="' + escapeHtml(d) + '"></i>' : '<span class="text-muted">-</span>';
        },

        /* 百分比：cfg = {decimals: 1, bar: true, variant: 'primary'}（bar=true 时进度条+数值） */
        percent: function (d, row, cfg) {
            var n = parseFloat(d) || 0;
            var txt = n.toFixed(cfg.decimals == null ? 1 : cfg.decimals) + '%';
            if (cfg.bar === false) return '<span class="fw-medium">' + txt + '</span>';
            var variant = cfg.variant || (n >= 80 ? 'success' : n >= 50 ? 'info' : n >= 30 ? 'warning' : 'danger');
            return '<div class="d-flex align-items-center gap-2" style="min-width:110px">' +
                '<div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-' + escapeHtml(variant) + '" style="width:' + Math.max(0, Math.min(100, n)) + '%"></div></div>' +
                '<span class="small text-muted">' + txt + '</span></div>';
        },

        /* 枚举映射：cfg.map = {value: {label:'待审核', color:'warning', icon:'ti ti-clock'}} 或 {value: '标签'} */
        'enum': function (d, row, cfg) {
            var m = (cfg.map || {})[d];
            if (m == null) return escapeHtml(str(d));
            if (typeof m === 'string') return '<span class="badge badge-soft-secondary">' + escapeHtml(m) + '</span>';
            var color = m.color || 'secondary';
            return '<span class="badge badge-soft-' + escapeHtml(color) + '">' +
                (m.icon ? '<i class="' + escapeHtml(m.icon) + ' me-1"></i>' : '') + escapeHtml(m.label || str(d)) + '</span>';
        },

        /* 邮箱（mailto 链接） / 电话（tel 链接） / 外链 URL */
        email: function (d) {
            var v = str(d);
            return v ? '<a href="mailto:' + escapeHtml(v) + '" class="text-body"><i class="ti ti-mail text-muted me-1"></i>' + escapeHtml(v) + '</a>' : '<span class="text-muted">-</span>';
        },
        phone: function (d) {
            var v = str(d);
            return v ? '<a href="tel:' + escapeHtml(v) + '" class="text-body font-monospace"><i class="ti ti-phone text-muted me-1"></i>' + escapeHtml(v) + '</a>' : '<span class="text-muted">-</span>';
        },
        url: function (d, row, cfg) {
            var v = str(d);
            if (!v) return '<span class="text-muted">-</span>';
            var show = v.replace(/^https?:\/\//, '');
            var len = cfg.length || 32;
            if (show.length > len) show = show.slice(0, len) + '…';
            return '<a href="' + escapeHtml(v) + '" target="_blank" rel="noopener" title="' + escapeHtml(v) + '">' + escapeHtml(show) + ' <i class="ti ti-external-link fs-12"></i></a>';
        },

        /* 用户单元格：头像+姓名+副标题。cfg = {avatar:'avatar_field', sub:'email_field', url:'/users/{id}'} */
        user: function (d, row, cfg) {
            var name = escapeHtml(str(d));
            var av = cfg.avatar ? row[cfg.avatar] : (row.avatar || row.photo || '');
            var sub = cfg.sub ? escapeHtml(str(row[cfg.sub] == null ? '' : row[cfg.sub])) : '';
            var img = av
                ? '<span class="avatar avatar-xs flex-shrink-0"><img src="' + escapeHtml(av) + '" class="img-fluid rounded-circle" alt=""></span>'
                : '<span class="avatar avatar-xs flex-shrink-0"><span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold">' + escapeHtml(str(d).charAt(0).toUpperCase()) + '</span></span>';
            var text = '<div class="d-flex flex-column lh-sm"><span class="fw-medium">' + name + '</span>' + (sub ? '<span class="text-muted fs-12">' + sub + '</span>' : '') + '</div>';
            var inner = '<div class="d-flex align-items-center gap-2 text-nowrap">' + img + text + '</div>';
            return cfg.url ? '<a href="' + XFAdmin.tpl(cfg.url, row) + '" class="text-body">' + inner + '</a>' : inner;
        },

        /* 多图缩略：值为数组或逗号分隔字符串。cfg = {max: 3, size: 28} */
        images: function (d, row, cfg) {
            var list = Array.isArray(d) ? d : str(d).split(',').filter(Boolean);
            if (!list.length) return '<span class="text-muted">-</span>';
            var max = cfg.max || 3, size = cfg.size || 28;
            var html = '<div class="d-flex align-items-center xf-cell-images">';
            list.slice(0, max).forEach(function (src) {
                html += '<img src="' + escapeHtml(String(src).trim()) + '" alt="" style="width:' + size + 'px;height:' + size + 'px" class="rounded border object-fit-cover">';
            });
            if (list.length > max) html += '<span class="badge badge-soft-secondary ms-1">+' + (list.length - max) + '</span>';
            return html + '</div>';
        },

        /* 数字：千分位。cfg = {decimals: 0, prefix: '', suffix: ''} */
        number: function (d, row, cfg) {
            var n = parseFloat(d);
            if (isNaN(n)) return '<span class="text-muted">-</span>';
            var s = n.toFixed(cfg.decimals || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return '<span class="font-monospace">' + escapeHtml((cfg.prefix || '') + s + (cfg.suffix || '')) + '</span>';
        },

        /* 文件大小：字节数自动换算 KB/MB/GB */
        filesize: function (d) {
            var n = parseFloat(d);
            if (isNaN(n)) return '<span class="text-muted">-</span>';
            var units = ['B', 'KB', 'MB', 'GB', 'TB'], i = 0;
            while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
            return '<span class="font-monospace">' + (i ? n.toFixed(1) : n) + ' ' + units[i] + '</span>';
        },

        /* 二维码（qr）：d 为内容字符串（URL / 文本），或 {text, size, ec, color, bg, download}
         * 依赖全局 qrcode 库（qrcode-generator）。ec 纠错级别 L/M/Q/H，size 像素，color/bg 前景/背景色。
         * 已统一 UTF-8 编码，支持中文等非 ASCII 内容。点击单元格放大查看；
         * 若内容为 http(s) 链接且 download!==false 额外显示「打开链接」按钮。 */
        qr: function (d, row, cfg) {
            if (d == null || d === '') return '<span class="text-muted">-</span>';
            var opt = (typeof d === 'object' && d !== null) ? d : {};
            var text = opt.text != null ? opt.text : (opt.url != null ? opt.url : (typeof d === 'string' ? d : ''));
            text = XFAdmin.tpl(String(text || ''), row);
            if (!text) return '<span class="text-muted">-</span>';
            var size = parseInt(opt.size || cfg.size || 88, 10) || 88;
            var ec = String(opt.ec || cfg.ec || 'M').toUpperCase();
            if (!/^[LMQH]$/.test(ec)) ec = 'M';
            var color = escapeHtml(opt.color || cfg.color || '#000000');
            var bg = escapeHtml(opt.bg || cfg.bg || '#ffffff');
            if (typeof qrcode !== 'function') {
                return '<span class="text-muted" title="二维码库未加载">' + escapeHtml(text.slice(0, 20)) + '</span>';
            }
            try {
                var qr = qrcode(0, ec);
                qr.addData(text);
                qr.make();
                var svg = qr.createSvgTag(2, 4, '', '')
                    .replace(/width="\d+px"/, 'width="100%"')
                    .replace(/height="\d+px"/, 'height="100%"')
                    .replace('fill="white"', 'fill="' + bg + '"')
                    .replace('fill="black"', 'fill="' + color + '"');
                var html = '<span class="xf-cell-qr" style="width:' + size + 'px;height:' + size + 'px;max-width:100%"'
                    + ' data-xf-qr="' + escapeHtml(text) + '" title="点击放大" role="button" tabindex="0">' + svg + '</span>';
                if (opt.download !== false && /^https?:/i.test(text)) {
                    html += ' <a class="btn btn-sm btn-soft-primary xf-qr-open" href="' + escapeHtml(text) + '" target="_blank" rel="noopener" title="打开链接"><i class="ti ti-external-link"></i></a>';
                }
                return html;
            } catch (e) {
                return '<span class="text-danger" title="' + escapeHtml(String((e && e.message) || e)) + '">QR 生成失败</span>';
            }
        },

        /* 文件：d 可为字符串 url，或 {url, name, size, icon}
         * 渲染为「图标 + 文件名 + 人类可读大小 + 下载按钮」，点击下载或新标签页打开 */
        file: function (d, row, cfg) {
            if (d == null || d === '') return '<span class="text-muted">-</span>';
            var f = (typeof d === 'string') ? { url: d } : (d || {});
            var url = f.url ? XFAdmin.tpl(f.url, row) : '';
            if (!url) return '<span class="text-muted">-</span>';
            var name = f.name ? XFAdmin.tpl(f.name, row) : (f.url || '').split('/').pop();
            var ext = (name.split('.').pop() || '').toLowerCase();
            var icon = f.icon || (/^(pdf)$/.test(ext) ? 'ti ti-file-type-pdf'
                : /^(doc|docx)$/.test(ext) ? 'ti ti-file-type-doc'
                : /^(xls|xlsx|csv)$/.test(ext) ? 'ti ti-file-type-xls'
                : /^(zip|rar|7z|tar|gz)$/.test(ext) ? 'ti ti-file-zip'
                : /^(png|jpe?g|gif|webp|svg)$/.test(ext) ? 'ti ti-photo'
                : /^(mp4|webm|mov|avi)$/.test(ext) ? 'ti ti-movie'
                : 'ti ti-file');
            var size = (f.size != null && f.size !== '') ? XFAdmin.cellRenderers.filesize(f.size) : '';
            var dl = (cfg.download !== false)
                ? '<a class="btn btn-sm btn-soft-secondary ms-2 xf-file-dl" href="' + escapeHtml(url) + '"'
                    + (f.download !== false ? ' download' : ' target="_blank"') + ' title="下载"><i class="ti ti-download"></i></a>'
                : '';
            return '<span class="d-inline-flex align-items-center gap-2">' +
                '<i class="' + escapeHtml(icon) + ' fs-5 text-secondary"></i>' +
                '<a class="link-primary text-truncate" style="max-width:180px" href="' + escapeHtml(url) + '" target="_blank" title="' + escapeHtml(name) + '">' + escapeHtml(name) + '</a>' +
                (size ? '<span class="text-muted small">' + size + '</span>' : '') + dl + '</span>';
        },

        /* 多人头像组：d 为数组，元素可为字符串 url 或 {url, name}
         * 最多显示 max（默认 3）个，超出折叠为 +N；name 参与 title 与无图回退首字母 */
        avatarGroup: function (d, row, cfg) {
            var list = Array.isArray(d) ? d : [];
            var max = cfg.max || 3;
            if (!list.length) return '<span class="text-muted">-</span>';
            var shown = list.slice(0, max), rest = list.length - shown.length;
            var html = '<span class="avatar-group">';
            shown.forEach(function (u) {
                var item = (typeof u === 'string') ? { url: u } : (u || {});
                var nm = item.name || '';
                var av = item.url
                    ? '<span class="avatar-group-item"><img src="' + escapeHtml(XFAdmin.tpl(item.url, row)) + '" class="rounded-circle" style="width:28px;height:28px;object-fit:cover" alt=""></span>'
                    : '<span class="avatar-group-item avatar-xs bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px">' + escapeHtml(String(nm || '?').charAt(0).toUpperCase()) + '</span>';
                html += av.replace('<span ', '<span title="' + escapeHtml(nm) + '" ');
            });
            if (rest > 0) {
                html += '<span class="avatar-group-item avatar-xs bg-light text-secondary rounded-circle d-inline-flex align-items-center justify-content-center">+' + rest + '</span>';
            }
            html += '</span>';
            return html;
        },

        /* 可编辑下拉单元格：cfg = {options: {v:'label'} , url: '/api/x/{id}', field: 'status'}（变更即提交） */
        select: function (d, row, cfg) {
            var html = '<select class="form-select form-select-sm xf-cell-select" style="min-width:96px"' +
                (cfg.url ? ' data-url="' + escapeHtml(XFAdmin.tpl(cfg.url, row)) + '"' : '') +
                (cfg.field ? ' data-field="' + escapeHtml(cfg.field) + '"' : '') + '>';
            var opts = cfg.options || {};
            Object.keys(opts).forEach(function (v) {
                html += '<option value="' + escapeHtml(v) + '"' + (String(d) === String(v) ? ' selected' : '') + '>' + escapeHtml(opts[v]) + '</option>';
            });
            return html + '</select>';
        },

        /* 悬浮提示（Bootstrap Tooltip）：cfg = {text: '提示模板 {field}', field, placement, icon, length} */
        tooltip: function (d, row, cfg) {
            var v = str(d);
            if (!v && !cfg.text) return '<span class="text-muted">-</span>';
            var tip = cfg.text ? tplRaw(cfg.text, row) : str(cfg.field ? row[cfg.field] : d);
            var show = cfg.length && v.length > cfg.length ? v.slice(0, cfg.length) + '…' : v;
            return '<span class="xf-tip-underline" data-bs-toggle="tooltip" data-bs-placement="' + escapeHtml(cfg.placement || 'top') + '" data-bs-title="' + escapeHtml(tip) + '">' +
                (cfg.icon ? '<i class="' + escapeHtml(cfg.icon) + ' me-1 text-muted"></i>' : '') + escapeHtml(show) + '</span>';
        },

        /* 气泡提示（Bootstrap Popover，点击/聚焦触发）：cfg = {title, content: '模板 {field}', field, label, icon, placement, trigger, html} */
        popover: function (d, row, cfg) {
            var content = cfg.content ? tplRaw(cfg.content, row) : str(cfg.field ? row[cfg.field] : d);
            if (!content) return '<span class="text-muted">-</span>';
            var title = cfg.title ? tplRaw(cfg.title, row) : '';
            var label = cfg.label ? tplRaw(cfg.label, row) : str(d);
            return '<a tabindex="0" role="button" class="xf-pointer link-primary text-decoration-none text-nowrap" data-bs-toggle="popover"' +
                ' data-bs-trigger="' + escapeHtml(cfg.trigger || 'focus') + '" data-bs-placement="' + escapeHtml(cfg.placement || 'top') + '"' +
                (title ? ' data-bs-title="' + escapeHtml(title) + '"' : '') +
                ' data-bs-content="' + escapeHtml(content) + '"' + (cfg.html ? ' data-bs-html="true"' : '') + '>' +
                '<i class="' + escapeHtml(cfg.icon || 'ti ti-info-circle') + ' me-1"></i>' + escapeHtml(label) + '</a>';
        },

        /* 按钮切换（点击在两种状态间切换，可选 AJAX 提交）：
         * cfg = {on:1, off:0, on_label, off_label, on_class, off_class, url({id}), field} */
        toggle: function (d, row, cfg) {
            var on = cfg.on !== undefined ? cfg.on : 1;
            var off = cfg.off !== undefined ? cfg.off : 0;
            var isOn = String(d) === String(on) || d === true;
            var onClass = cfg.on_class || 'btn-success';
            var offClass = cfg.off_class || 'btn-outline-secondary';
            var onLabel = cfg.on_label || '启用';
            var offLabel = cfg.off_label || '停用';
            return '<button type="button" class="btn btn-sm xf-cell-toggle ' + escapeHtml(isOn ? onClass : offClass) + '"' +
                ' data-xf-state="' + (isOn ? '1' : '0') + '"' +
                ' data-xf-on="' + escapeHtml(str(on)) + '" data-xf-off="' + escapeHtml(str(off)) + '"' +
                ' data-xf-on-label="' + escapeHtml(onLabel) + '" data-xf-off-label="' + escapeHtml(offLabel) + '"' +
                ' data-xf-on-class="' + escapeHtml(onClass) + '" data-xf-off-class="' + escapeHtml(offClass) + '"' +
                (cfg.url ? ' data-xf-url="' + escapeHtml(XFAdmin.tpl(cfg.url, row)) + '"' : '') +
                ' data-xf-field="' + escapeHtml(cfg.field || 'status') + '" data-xf-id="' + escapeHtml(row && row.id != null ? row.id : '') + '">' +
                '<i class="ti ti-' + (isOn ? 'check' : 'ban') + ' me-1"></i>' + escapeHtml(isOn ? onLabel : offLabel) + '</button>';
        },

        /* 状态点：cfg.map = {value: {label, color}} 或 {value: 'color'} */
        status: function (d, row, cfg) {
            var m = (cfg.map || {})[d];
            var color = 'secondary', label = str(d);
            if (typeof m === 'string') { color = m; }
            else if (m) { color = m.color || 'secondary'; label = m.label != null ? m.label : label; }
            return '<span class="d-inline-flex align-items-center gap-1 text-nowrap"><span class="xf-status-dot bg-' + escapeHtml(color) + '"></span>' + escapeHtml(label) + '</span>';
        },

        /* 涨跌趋势：正值绿升 / 负值红降。cfg = {suffix: '%', decimals: 1, invert} */
        trend: function (d, row, cfg) {
            var n = parseFloat(d);
            if (isNaN(n)) return '<span class="text-muted">-</span>';
            var up = cfg.invert ? n < 0 : n > 0;
            var cls = n === 0 ? 'text-muted' : (up ? 'text-success' : 'text-danger');
            var icon = n === 0 ? 'ti ti-minus' : (n > 0 ? 'ti ti-trending-up' : 'ti ti-trending-down');
            var txt = (n > 0 ? '+' : '') + n.toFixed(cfg.decimals == null ? 1 : cfg.decimals) + (cfg.suffix == null ? '%' : cfg.suffix);
            return '<span class="' + cls + ' text-nowrap fw-medium"><i class="' + icon + ' me-1"></i>' + escapeHtml(txt) + '</span>';
        },

        /* 迷你趋势图（内联 SVG，无第三方依赖）：值为数字数组或逗号分隔串。cfg = {type: 'line'|'bar', width, height, color} */
        sparkline: function (d, row, cfg) {
            var arr = (Array.isArray(d) ? d : str(d).split(',')).map(function (x) { return parseFloat(x); }).filter(function (x) { return isFinite(x); });
            if (arr.length < 2) return '<span class="text-muted">-</span>';
            var w = cfg.width || 90, h = cfg.height || 24;
            var max = Math.max.apply(null, arr), min = Math.min.apply(null, arr);
            var range = (max - min) || 1;
            var color = escapeHtml(cfg.color || '#3e60d5');
            if (cfg.type === 'bar') {
                var bw = w / arr.length;
                var bars = arr.map(function (v, i) {
                    var bh = Math.max(1, (v - min) / range * (h - 2));
                    return '<rect x="' + (i * bw + 1).toFixed(1) + '" y="' + (h - bh).toFixed(1) + '" width="' + Math.max(1, bw - 2).toFixed(1) + '" height="' + bh.toFixed(1) + '" rx="1" fill="' + color + '"></rect>';
                }).join('');
                return '<svg class="xf-spark" width="' + w + '" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '">' + bars + '</svg>';
            }
            var pts = arr.map(function (v, i) {
                return (i / (arr.length - 1) * (w - 2) + 1).toFixed(1) + ',' + ((1 - (v - min) / range) * (h - 4) + 2).toFixed(1);
            }).join(' ');
            var last = arr[arr.length - 1];
            var lastY = ((1 - (last - min) / range) * (h - 4) + 2).toFixed(1);
            return '<svg class="xf-spark" width="' + w + '" height="' + h + '" viewBox="0 0 ' + w + ' ' + h + '">' +
                '<polyline points="' + pts + '" fill="none" stroke="' + color + '" stroke-width="1.5"></polyline>' +
                '<circle cx="' + (w - 1) + '" cy="' + lastY + '" r="2" fill="' + color + '"></circle></svg>';
        },

        /* 单元格时间线：值为 [{time,title,text,color}] 或字符串数组。cfg = {max: 2, title}
         * 超过 max 条时显示"查看全部"按钮，点击弹出完整时间线 */
        timeline: function (d, row, cfg) {
            var list = Array.isArray(d) ? d : [];
            if (!list.length) return '<span class="text-muted">-</span>';
            var max = cfg.max || 2;
            var html = '<div class="xf-cell-tl">';
            list.slice(0, max).forEach(function (ev) {
                var t = typeof ev === 'string' ? { title: ev } : (ev || {});
                html += '<div class="xf-cell-tl-item"><span class="xf-cell-tl-dot bg-' + escapeHtml(t.color || 'primary') + '"></span>' +
                    (t.time ? '<span class="text-muted fs-12 me-1">' + escapeHtml(t.time) + '</span>' : '') +
                    '<span>' + escapeHtml(t.title || t.text || '') + '</span></div>';
            });
            if (list.length > max) {
                html += '<button type="button" class="btn btn-link btn-sm p-0 fs-12 xf-cell-tl-more" data-xf-tl="' + escapeHtml(JSON.stringify(list)) + '"' +
                    ' data-xf-tl-title="' + escapeHtml(cfg.title || '完整时间线') + '">查看全部 ' + list.length + ' 条 <i class="ti ti-chevron-right fs-12"></i></button>';
            }
            return html + '</div>';
        },

        /* 下拉按钮组（单个按钮，点击展开操作菜单）：cfg = {label, icon, class, items: [同 actions 子项]} */
        dropdown: function (d, row, cfg) {
            return XFAdmin.cellRenderers.actions(d, row, {
                items: [{
                    label: cfg.label || '操作',
                    icon: cfg.icon || 'ti ti-settings',
                    class: cfg.class || 'btn-soft-secondary',
                    dropdown: cfg.items || []
                }]
            });
        },

        /* 行操作栏：cfg.items = [{label, icon, class, url, ajax, method, confirm, reload, action, dropdown, view, fields}] */
        actions: function (d, row, cfg) {
            var html = '<div class="btn-group btn-group-sm xf-row-actions" role="group">';
            (cfg.items || []).forEach(function (item, idx) {
                if (item.dropdown) {
                    html += '<div class="btn-group btn-group-sm" role="group">' +
                        '<button type="button" class="btn ' + escapeHtml(item.class || 'btn-soft-secondary') + ' dropdown-toggle" data-bs-toggle="dropdown">' +
                        (item.icon ? '<i class="' + escapeHtml(item.icon) + '"></i> ' : '') + escapeHtml(item.label || '更多') + '</button><ul class="dropdown-menu">';
                    item.dropdown.forEach(function (sub, sidx) {
                        if (sub.type === 'divider') { html += '<li><hr class="dropdown-divider"></li>'; return; }
                        html += '<li>' + actionAnchor(sub, row, idx + '-' + sidx, 'dropdown-item') + '</li>';
                    });
                    html += '</ul></div>';
                    return;
                }
                html += actionAnchor(item, row, String(idx), 'btn ' + (item.class || 'btn-soft-primary'));
            });
            return html + '</div>';

            function actionAnchor(item, row, key, cls) {
                var attrs = ' class="' + escapeHtml(cls) + '"';
                var inner = (item.icon ? '<i class="' + escapeHtml(item.icon) + '"></i>' : '') +
                    (item.label && item.icon ? ' ' : '') + (item.label ? escapeHtml(item.label) : '');
                if (item.title) attrs += ' title="' + escapeHtml(item.title) + '"';
                // 行编辑：action=edit（弹出模态表单，保存后自动刷新表格）
                if (item.action === 'edit') {
                    var editCfg = {
                        url: item.ajax ? XFAdmin.tpl(item.ajax, row) : '',
                        method: item.method || 'PUT',
                        title: (item.editTitle || item.title) ? tplRaw(item.editTitle || item.title, row) : '编辑',
                        fields: item.fields || null,
                        reload: item.reload !== false,
                        page: item.page ? XFAdmin.tpl(item.page, row) : '',
                        frame: !!item.frame,
                        size: item.size || '',
                        maximizable: item.maximizable !== false,
                        viewTitle: item.viewTitle || ''
                    };
                    attrs += ' data-xf-act="edit" data-xf-edit="' + escapeHtml(JSON.stringify(editCfg)) + '"';
                    return '<button type="button"' + attrs + '>' + inner + '</button>';
                }
                // 行详情：action=view（item.view 支持个性化详情布局：layout/sections/header/template/ajax 等）
                if (item.action === 'view' && (item.view || item.viewTitle)) {
                    attrs += ' data-xf-view="' + escapeHtml(JSON.stringify(item.view || { title: item.viewTitle })) + '"';
                }
                if (item.url) {
                    return '<a href="' + XFAdmin.tpl(item.url, row) + '"' + attrs + (item.target ? ' target="' + escapeHtml(item.target) + '"' : '') + '>' + inner + '</a>';
                }
                attrs += ' data-xf-act="' + escapeHtml(item.action || 'ajax') + '"';
                if (item.ajax) attrs += ' data-xf-url="' + escapeHtml(XFAdmin.tpl(item.ajax, row)) + '"';
                if (item.method) attrs += ' data-xf-method="' + escapeHtml(item.method) + '"';
                if (item.confirm) attrs += ' data-xf-confirm="' + escapeHtml(item.confirm) + '"';
                if (item.confirm_popover) attrs += ' data-xf-confirm-popover="1"';
                if (item.reload) attrs += ' data-xf-reload="1"';
                if (item.event) attrs += ' data-xf-event="' + escapeHtml(item.event) + '"';
                return '<button type="button"' + attrs + '>' + inner + '</button>';
            }
        }
    };

    /* 按钮组别名（与 actions 等价，语义化命名） */
    XFAdmin.cellRenderers.buttons = XFAdmin.cellRenderers.actions;

    XFAdmin.scan = function (root) {
        root = root || document;
        var nodes = root.querySelectorAll('[data-xf]');
        Array.prototype.forEach.call(nodes, function (el) {
            if (el.__xfInited) return;
            var name = el.getAttribute('data-xf');
            var fn = XFAdmin.widgets[name];
            if (!fn) return;
            el.__xfInited = true;
            try {
                var instance = fn(el, readConfig(el));
                if (instance) XFAdmin.instances.set(el, instance);
            } catch (e) {
                console.error('[XfAdmin] 初始化组件失败: ' + name, el, e);
            }
        });
        // 图标统一使用 Tabler(ti) 字体图标，无需 lucide 增量渲染
    };

    /** 获取元素上的插件实例 */
    XFAdmin.get = function (el) {
        if (typeof el === 'string') el = document.querySelector(el);
        return el ? XFAdmin.instances.get(el) : undefined;
    };

    /* ------------------------------------------------------------------
     * 内置组件初始化器
     * ---------------------------------------------------------------- */

    // ---------- DataTable 列显隐下拉（colvis 模块未随包发布，自行实现） ----------
    function xfColvisToggle(dt, btn) {
        var existing = document.getElementById('xf-colvis-menu');
        if (existing) {
            if (existing._xfClose) document.removeEventListener('click', existing._xfClose);
            existing.remove();
            return;
        }
        var menu = document.createElement('div');
        menu.id = 'xf-colvis-menu';
        menu.className = 'dropdown-menu show p-2 shadow';
        menu.style.position = 'absolute';
        menu.style.zIndex = 2100;
        var r = btn.getBoundingClientRect();
        menu.style.top = (window.pageYOffset + r.bottom + 4) + 'px';
        menu.style.left = (window.pageXOffset + r.left) + 'px';
        menu.style.minWidth = '200px';
        dt.columns().every(function (i) {
            var col = this;
            var header = col.header();
            var title = (header ? header.textContent.trim() : '') || ('列 ' + (i + 1));
            if (!title) return;
            var wrap = document.createElement('div');
            wrap.className = 'form-check mb-1';
            var cb = document.createElement('input');
            cb.type = 'checkbox'; cb.className = 'form-check-input me-1'; cb.checked = col.visible();
            cb.id = 'xf-colvis-' + i;
            cb.addEventListener('change', function () { col.visible(cb.checked); });
            var lab = document.createElement('label');
            lab.className = 'form-check-label small'; lab.htmlFor = cb.id; lab.textContent = title;
            wrap.appendChild(cb); wrap.appendChild(lab);
            menu.appendChild(wrap);
        });
        document.body.appendChild(menu);
        setTimeout(function () {
            var onDoc = function (ev) {
                if (menu && !menu.contains(ev.target) && ev.target !== btn) {
                    document.removeEventListener('click', onDoc);
                    menu.remove();
                }
            };
            menu._xfClose = onDoc;
            document.addEventListener('click', onDoc);
        }, 0);
    }

    // ---------- DataTable（含模板列 / 徽章列 / 富渲染器 / 列筛选） ----------
    XFAdmin.register('datatable', function (el, config) {
        if (!global.DataTable) return;
        var dt = config.dt || {};

        // 注册缺失的 colvis 按钮类型（其扩展模块未随包发布），避免
        // “Cannot extend unknown button type: colvis” 导致整个初始化抛出并中断
        var XF_DT = global.DataTable;
        if (XF_DT.ext && XF_DT.ext.buttons && !XF_DT.ext.buttons.colvis) {
            XF_DT.ext.buttons.colvis = {
                text: '<i class="ti ti-columns"></i> 列显示',
                className: 'btn btn-sm btn-secondary buttons-colvis',
                action: function (e, api, node) {
                    if (e && e.preventDefault) e.preventDefault();
                    if (e && e.stopPropagation) e.stopPropagation();
                    var btnEl = node && node.get ? node.get(0) : node;
                    xfColvisToggle(api, btnEl);
                }
            };
        }
        // 注册 xfButton 基础类型，供 refresh / fullscreen 包内扩展按钮使用，
        // 避免 “Cannot extend unknown button type: xfButton”
        if (XF_DT.ext && XF_DT.ext.buttons && !XF_DT.ext.buttons.xfButton) {
            XF_DT.ext.buttons.xfButton = {
                text: '',
                action: function () {}
            };
        }
        // print / printHtml5 互为别名（历史配置里写过 printHtml5，但打印扩展注册的类型名是 print），
        // 并在打印扩展缺失时兜底为 window.print，避免 “Cannot extend unknown button type” 中断初始化
        if (XF_DT.ext && XF_DT.ext.buttons) {
            var B = XF_DT.ext.buttons;
            if (!B.print) {
                B.print = B.printHtml5 || {
                    text: '<i class="ti ti-printer"></i> 打印',
                    className: 'btn btn-sm btn-secondary buttons-print',
                    action: function () { window.print(); }
                };
            }
            if (!B.printHtml5) B.printHtml5 = B.print;
        }

        // 缺省中文语言包（配置传入的 language 优先）
        dt.language = Object.assign({}, XFAdmin.dtLanguage, dt.language || {});

        // 列元信息（label + 渲染函数），供行详情弹窗（viewRow）复用列渲染与中文列名
        var colMeta = {};
        (dt.columns || []).forEach(function (col) {
            if (col.xfTemplate) {
                var tpl = col.xfTemplate;
                col.render = function (data, type, row) {
                    if (type && type !== 'display') return data;
                    return XFAdmin.tpl(tpl, row);
                };
                if (col.orderable === undefined) col.orderable = false;
                delete col.xfTemplate;
            }
            if (col.xfBadges) {
                var badges = col.xfBadges;
                col.render = function (data, type) {
                    if (type && type !== 'display') return data;
                    var variant = badges[data];
                    var safe = escapeHtml(data);
                    return variant ? '<span class="badge bg-' + variant + '-subtle text-' + variant + '">' + safe + '</span>' : safe;
                };
                delete col.xfBadges;
                if (typeof col.data === 'string' && col.data) colMeta[col.data] = { render: col.render };
            }
            if (col.xfRender) {
                var cfg = col.xfRender;
                // 渲染器解析优先级：内置/已注册渲染器 -> 'js:函数名' 开发者自定义全局函数 -> 纯文本兜底。
                // 'js:' 函数签名与渲染器一致：fn(data, row, cfg, meta) => HTML 字符串；支持点号路径（如 js:App.render.money）
                var renderer = XFAdmin.cellRenderers[cfg.type];
                if (!renderer && typeof cfg.type === 'string' && cfg.type.indexOf('js:') === 0) {
                    var fnPath = cfg.type.slice(3);
                    renderer = function (data, row, c, meta) {
                        var fn = fnPath.split('.').reduce(function (o, k) { return o ? o[k] : null; }, global);
                        if (typeof fn === 'function') {
                            try { return fn(data, row, c, meta); } catch (err) { /* 自定义渲染异常回退文本 */ }
                        }
                        return XFAdmin.cellRenderers.text(data, row, c, meta);
                    };
                }
                renderer = renderer || XFAdmin.cellRenderers.text;
                col.render = function (data, type, row, meta) {
                    if (type && type !== 'display') return cfg.type === 'actions' ? '' : data;
                    return renderer(data, row || {}, cfg, meta);
                };
                delete col.xfRender;
                // 交互/操作类渲染器不适合在详情弹窗中静态展示，不纳入详情复用
                var interactive = { actions: 1, buttons: 1, dropdown: 1, input: 1, select: 1, 'switch': 1, toggle: 1, timeline: 1 };
                if (typeof col.data === 'string' && col.data && !interactive[cfg.type]) {
                    colMeta[col.data] = { render: col.render };
                }
            }
        });

        // 包内扩展按钮（refresh / fullscreen / density）
        (dt.buttons || []).forEach(function (btn, i) {
            if (!btn || !btn.xfButton) return;
            var kind = btn.xfButton;
            delete btn.xfButton;
            if (kind === 'refresh') {
                btn.text = '<i class="ti ti-refresh"></i> 刷新';
                btn.action = function (e, api) { api.ajax && api.ajax.url() ? api.ajax.reload(null, false) : api.draw(false); };
            } else if (kind === 'fullscreen') {
                btn.text = '<i class="ti ti-maximize"></i> 全屏';
                btn.action = function () {
                    var card = el.closest('.card') || el.closest('.table-responsive') || el.parentElement;
                    if (!document.fullscreenElement) { card.requestFullscreen && card.requestFullscreen(); }
                    else { document.exitFullscreen && document.exitFullscreen(); }
                };
            } else if (kind === 'density') {
                var isCompact = el.classList.contains('xf-dt-compact');
                btn.text = isCompact
                    ? '<i class="ti ti-rows-2"></i> 宽松'
                    : '<i class="ti ti-rows-3"></i> 紧凑';
                btn.action = function () {
                    var compact = el.classList.toggle('xf-dt-compact');
                    var btnEl = el.closest('.card') ? el.closest('.card').querySelector('.xf-btn-density') : null;
                    if (btnEl) {
                        btnEl.innerHTML = compact
                            ? '<i class="ti ti-rows-2"></i> 宽松'
                            : '<i class="ti ti-rows-3"></i> 紧凑';
                    }
                };
            }
        });

        // serverSide 模式：压缩 DataTables 协议参数（columns/order/search 数组转紧凑串）。
        // 多列表格的原生参数可超过 3KB，易触发 WAF/服务器 URL 长度限制（403/414），
        // 压缩后由服务端 DataSet::parseRequest 自动还原（xfc/xfo/xfs）。支持 method=POST。
        if (dt.serverSide && dt.ajax) {
            var ajaxCfg = (typeof dt.ajax === 'string') ? { url: dt.ajax } : dt.ajax;
            if (!ajaxCfg.data) {
                ajaxCfg.data = function (d) {
                    var enc = encodeURIComponent;
                    if (d && d.columns) {
                        d.xfc = d.columns.map(function (c) {
                            return enc(c.data == null ? '' : c.data) + ':' + (c.searchable ? 1 : 0) + ':' + (c.orderable ? 1 : 0) +
                                (c.search && c.search.value ? ':' + enc(c.search.value) : '');
                        }).join('|');
                        delete d.columns;
                    }
                    if (d && d.order) {
                        d.xfo = d.order.map(function (o) { return o.column + ':' + (o.dir === 'desc' ? 'd' : 'a'); }).join('|');
                        delete d.order;
                    }
                    if (d && d.search) {
                        if (d.search.value) d.xfs = d.search.value;
                        delete d.search;
                    }
                    return d;
                };
            }
            var ajaxMethod = String(ajaxCfg.type || ajaxCfg.method || 'GET').toUpperCase();
            if (ajaxMethod === 'POST') {
                ajaxCfg.type = 'POST';
                var csrfTk = XFAdmin.csrf();
                if (csrfTk) ajaxCfg.headers = Object.assign({ 'X-CSRF-TOKEN': csrfTk }, ajaxCfg.headers || {});
            }
            dt.ajax = ajaxCfg;
        }

        // AJAX 错误优雅提示（阻止 DataTables 原生 alert 弹窗，参照 yoc_cn TableTools 错误处理）
        try { global.DataTable.ext.errMode = 'none'; } catch (e) { /* noop */ }
        var dtContainer = el.closest('.dt-container');
        el.addEventListener('error.dt', function () {
            // 设置 .dt-empty 为错误信息（yoc_cn 模式）
            var tmp = dtContainer ? dtContainer.querySelector('.dt-empty') : null;
            if (tmp) tmp.innerHTML = '<div class="text-danger"><i class="ti ti-alert-circle"></i> 数据加载失败，请稍后重试</div>';
            // 隐藏 processing 遮罩
            var processing = dtContainer ? dtContainer.querySelector('.dt-processing') : null;
            if (processing) processing.style.display = 'none';
            XFAdmin.toast({ body: '表格数据加载失败，请检查网络或稍后重试', variant: 'danger' });
        });
        if (global.jQuery) {
            global.jQuery(el).on('error.dt', function (e, settings, techNote, message) {
                console.warn('[XfAdmin] DataTables:', message);
            });
        }

        // 智能 dataSrc：兼容多种服务端返回格式（参照 yoc_cn TableTools）
        // 支持格式：{data:[...], recordsTotal:...} | {list:[...], total:...} | {rows:[...], count:...} | 纯数组
        if (dt.ajax && !(typeof dt.ajax === 'object' && dt.ajax.dataSrc)) {
            var srcAjax = typeof dt.ajax === 'string' ? { url: dt.ajax } : (dt.ajax || {});
            var origDataSrc = srcAjax.dataSrc;
            if (!origDataSrc || origDataSrc === 'data') {
                srcAjax.dataSrc = function (res) {
                    if (Array.isArray(res)) return res;
                    if (res && res.data) return res.data;
                    if (res && res.list)    return res.list;
                    if (res && res.rows)    return res.rows;
                    return res || [];
                };
            }
            // 同时处理 recordsFiltered/recordsTotal 在不同格式下的键名
            var origDataFiltered = srcAjax.dataFiltered;
            var origDataTotal = srcAjax.dataTotal;
            // 这些字段 DataTables 会自动从返回对象读取同名属性，
            // 但如果服务端用其他键名（如 total/count），需要在 dataSrc 中注入。
            // 此处保留兼容层：支持返回对象含 recordsTotal/dataTotal/total 任一种。
            dt.ajax = srcAjax;
        }

        // processing 遮罩层显示/隐藏（参照 yoc_cn）：xhr 请求期间展示加载状态
        if (dt.processing !== false) {
            var _procContainer = dtContainer;
            if (global.jQuery) {
                global.jQuery(el).on('preXhr.dt', function () {
                    var proc = _procContainer ? _procContainer.querySelector('.dt-processing') : null;
                    if (proc) proc.style.display = '';
                });
                global.jQuery(el).on('xhr.dt', function () {
                    var proc = _procContainer ? _procContainer.querySelector('.dt-processing') : null;
                    if (proc) proc.style.display = 'none';
                });
            }
        }

        // 行分组（仅本地/非服务端数据生效）：每次绘制时按指定字段插入分组标题行
        if (config.rowGroup && dt.serverSide !== true) {
            var rgField = config.rowGroup.data;
            var rgEmpty = config.rowGroup.empty != null ? config.rowGroup.empty : '（未分组）';
            dt.drawCallback = function () {
                var api = this.api();
                var tbodyEl = api.table().node().querySelector('tbody');
                if (tbodyEl) {
                    // 先清除上一轮插入的分组行，避免叠加
                    var olds = tbodyEl.querySelectorAll('tr.xf-dt-group-row');
                    for (var oi = 0; oi < olds.length; oi++) { olds[oi].remove(); }
                }
                var rows = api.rows({ page: 'current' }).nodes();
                var data = api.rows({ page: 'current' }).data();
                // colspan 取可见列数（列显隐切换后仍能整行铺满）
                var colN = api.columns(':visible').header().length || api.columns().header().length;
                var last = undefined;
                for (var gi = 0; gi < data.length; gi++) {
                    var cur = data[gi] ? data[gi][rgField] : undefined;
                    var key = (cur == null || cur === '') ? rgEmpty : cur;
                    if (last === undefined || last !== key) {
                        global.jQuery(rows).eq(gi).before(
                            '<tr class="xf-dt-group-row"><td colspan="' + colN + '">' +
                            '<span class="xf-dt-group-label">' + escapeHtml(String(key)) + '</span></td></tr>'
                        );
                        last = key;
                    }
                }
            };
        }

        // createdRow 回调（参照 yoc_cn TableTools.createdRow）
        if (dt.createdRow) {
            var _origCreatedRow = dt.createdRow;
            dt.createdRow = function (row, data, dataIndex) {
                _origCreatedRow.call(this, row, data, dataIndex);
                // 调用 config 中指定的全局 createdRow 回调
                if (config.createdRow && typeof global[config.createdRow] === 'function') {
                    try { global[config.createdRow](row, data, dataIndex); } catch (e) { console.warn('[XfAdmin] createdRow error:', e); }
                }
            };
        } else if (config.createdRow && typeof global[config.createdRow] === 'function') {
            dt.createdRow = function (row, data, dataIndex) {
                try { global[config.createdRow](row, data, dataIndex); } catch (e) { console.warn('[XfAdmin] createdRow error:', e); }
            };
        }

        // drawCallback 包装：rowGroup 用 drawCallback，同时支持用户自定义回调（参照 yoc_cn TableTools.drawCallback）
        var _hasRowGroupDraw = !!dt.drawCallback;
        var _userDrawCallback = config.drawCallback && typeof global[config.drawCallback] === 'function' ? global[config.drawCallback] : null;
        if (_userDrawCallback && _hasRowGroupDraw) {
            var _origDraw = dt.drawCallback;
            dt.drawCallback = function (settings) {
                _origDraw.call(this, settings);
                try { _userDrawCallback.call(this, settings); } catch (e) { console.warn('[XfAdmin] drawCallback error:', e); }
            };
        } else if (_userDrawCallback && !_hasRowGroupDraw) {
            dt.drawCallback = function (settings) {
                try { _userDrawCallback.call(this, settings); } catch (e) { console.warn('[XfAdmin] drawCallback error:', e); }
            };
        }

        // 提前检测 show_detail 列（DT 初始化后 columns 配置可能被改写）
        var _showDetailCols = [];
        try {
            var _cols = dt.columns || [];
            for (var _di = 0; _di < _cols.length; _di++) {
                if (_cols[_di] && _cols[_di].showDetail) _showDetailCols.push(_di);
            }
        } catch (e) { /* noop */ }

        // show_custom_search 搜索表单展开/收起切换（参照 yoc_cn）
        var _showCustomSearch = !!(config.showCustomSearch || (dt.show_custom_search));
        if (_showCustomSearch) {
            var _searchForm = dtContainer ? dtContainer.querySelector('.xf-dt-search-form,.custom-datatable-search') : null;
            if (!_searchForm && dtContainer) {
                // 为没有服务端预渲染搜索表单的情况，在容器前插入一个空占位容器
                var _sf = document.createElement('div');
                _sf.className = 'custom-datatable-search';
                _sf.style.display = 'none';
                dtContainer.insertAdjacentElement('beforebegin', _sf);
            }
            // 监听搜索切换按钮（.date-table-tools-search-btn）
            if (global.jQuery) {
                global.jQuery(document).on('click', '.date-table-tools-search-btn', function () {
                    var btn = global.jQuery(this);
                    var pid = btn.closest('[data-dt-target]').data('dt-target') || btn.closest('.dt-container').prev('.custom-datatable-search');
                    var searchEl;
                    if (typeof pid === 'string' && pid) {
                        searchEl = document.querySelector(pid);
                    } else if (pid && pid.jquery) {
                        searchEl = pid[0];
                    }
                    if (!searchEl) {
                        searchEl = dtContainer ? dtContainer.parentElement.querySelector('.custom-datatable-search') : null;
                    }
                    if (searchEl) {
                        searchEl.classList.toggle('show');
                    }
                    btn.find('.ti, i').toggleClass('ti-search ti-x');
                });
            }
        }

        var table;
        try {
            table = new global.DataTable(el, dt);
            el.__xfTable = table;
        } catch (err) {
            console.error('[XfAdmin] 初始化表格失败:', err);
            XFAdmin.toast({ body: '表格初始化失败：' + (err && err.message ? err.message : err), variant: 'danger' });
            return null;
        }

        // 补充列头中文名到列元信息，并挂到表格元素供 viewRow 使用
        try {
            table.columns().every(function () {
                var src = this.dataSrc();
                if (typeof src === 'string' && src) {
                    colMeta[src] = colMeta[src] || {};
                    if (!colMeta[src].label) {
                        var hEl = this.header();
                        colMeta[src].label = hEl ? hEl.textContent.trim() : src;
                    }
                }
            });
        } catch (metaErr) { /* noop */ }
        el.__xfColMeta = colMeta;

        // show_detail：dt-control 子行展开（参照 yoc_cn TableTools show_detail 两种模式）
        // 模式1：callback——列配置 showDetail 为函数，调用回调生成 HTML
        // 模式2：auto——showDetail 为 true/字符串，自动渲染所有列或指定字段
        if (_showDetailCols.length > 0 && global.jQuery) {
            global.jQuery(el).on('click', 'td.dt-control', function () {
                var tr = global.jQuery(this).closest('tr');
                var row = table.row(tr);
                if (!row.child.isShown()) {
                    var rowData = row.data();
                    var colIdx = table.cell(this).index().column;
                    var colSettings = table.settings().init().columns[colIdx] || {};
                    var html = '';
                    // 模式1：回调函数（参照 yoc_cn：row.context[0].aoColumns[col].show_detail(data, idx, row, table)）
                    if (typeof colSettings.showDetail === 'function') {
                        try {
                            var cbHtml = colSettings.showDetail(rowData, row.index(), row, table);
                            html = '<div class="table-row-detail">' + (cbHtml || '') + '</div>';
                        } catch (cbErr) {
                            html = '<div class="table-row-detail text-danger">详情渲染失败</div>';
                        }
                    } else if (typeof colSettings.showDetail === 'string' && colSettings.showDetail) {
                        // 模式2a：特定字段名，只展示该字段
                        var fieldVal = rowData ? rowData[colSettings.showDetail] : null;
                        if (fieldVal !== undefined && fieldVal !== null) {
                            if (typeof fieldVal === 'object') fieldVal = JSON.stringify(fieldVal);
                            html = '<div class="table-row-detail">' + escapeHtml(String(fieldVal)) + '</div>';
                        } else {
                            html = '<div class="table-row-detail">无详情</div>';
                        }
                    } else {
                        // 模式2b：自动渲染所有可见列键值对
                        html = '<div class="table-row-detail"><dl>';
                        if (rowData && el.__xfColMeta) {
                            Object.keys(el.__xfColMeta).forEach(function (key) {
                                var meta = el.__xfColMeta[key];
                                var label = (meta && meta.label) ? meta.label : key;
                                var val = (rowData[key] !== undefined && rowData[key] !== null) ? rowData[key] : '-';
                                if (typeof val === 'object') val = JSON.stringify(val);
                                html += '<dt>' + escapeHtml(String(label)) + '</dt><dd>' + escapeHtml(String(val)) + '</dd>';
                            });
                        }
                        html += '</dl></div>';
                    }
                    row.child(html).show();
                    tr.addClass('dt-hasChild');
                } else {
                    row.child.hide();
                    tr.removeClass('dt-hasChild');
                }
            });
        }

        // 单元格 Tooltip / Popover 渲染器需要在每次绘制后初始化 Bootstrap 实例
        var initTips = function () {
            if (!global.bootstrap) return;
            if (global.bootstrap.Tooltip) {
                el.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (n) {
                    global.bootstrap.Tooltip.getOrCreateInstance(n);
                });
            }
            if (global.bootstrap.Popover) {
                el.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (n) {
                    global.bootstrap.Popover.getOrCreateInstance(n);
                });
            }
        };
        if (global.jQuery) global.jQuery(el).on('draw.dt', function () { setTimeout(initTips, 0); });
        setTimeout(initTips, 0);

        // === DataTables 2.x scrollX 列宽同步修复（参照 yoc_cn 项目 TableTools） ===
        // 核心：DataTables 2.x scrollX 创建独立表头(.dt-scroll-head)和表体(.dt-scroll-body)容器。
        // 两独立 table 的列宽对齐依赖三个层面协同：
        //   - CSS: 覆盖 INSPINIA display:none!important（保布局流）+ table-layout:fixed（统一计算模式）
        //   - JS: ResizeObserver + columns.adjust() 在容器/尺寸变化时重算列宽
        //   - Event: 初始化/draw/Tab切换/窗口resize 四个时机触发同步
        // 注意：必须用 table.on() 事件（DataTable 已创建，不能用 dt.initComplete 重绑定）

        // 1) 初始化后同步列宽（requestAnimationFrame 确保布局计算完成后再 adjust）
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                try { table.columns.adjust(); } catch (e) { /* noop */ }
            });
        });

        // 2) draw.dt 事件：每次重绘后同步
        if (global.jQuery) {
            global.jQuery(el).on('draw.dt', function () {
                setTimeout(function () {
                    try { table.columns.adjust(); } catch (e) { /* noop */ }
                }, 50);
            });
        }

        // 3) ResizeObserver：每个 DT 容器独立监听，维度变化时同步（200ms 防抖）
        //    多个表格共享一个 Observer 实例（性能最优）；每个表格的 .dt-container 都注册
        if ('ResizeObserver' in window) {
            if (!XFAdmin._dtResizeObserver) {
                XFAdmin._dtResizeTimer = null;
                XFAdmin._dtResizeObserver = new ResizeObserver(function () {
                    clearTimeout(XFAdmin._dtResizeTimer);
                    XFAdmin._dtResizeTimer = setTimeout(function () {
                        try {
                            DataTable.tables({ visible: true, api: true }).columns.adjust();
                        } catch (e) { /* noop */ }
                    }, 200);
                });
            }
            // DT 初始化后 .dt-container 已存在，延迟注册确保 wrapper DOM 就绪
            setTimeout(function () {
                var container = el.closest('.dt-container');
                if (container) {
                    XFAdmin._dtResizeObserver.observe(container);
                }
            }, 50);
        }

        // 4) Bootstrap Tab 切换时同步所有可见表（yoc_cn 模式）
        if (!XFAdmin._dtTabBound) {
            XFAdmin._dtTabBound = true;
            document.addEventListener('shown.bs.tab', function () {
                // 双重 RAF 确保 Tab 容器完成布局后再 adjust
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        try {
                            DataTable.tables({ visible: true, api: true }).columns.adjust();
                        } catch (e) { /* noop */ }
                    });
                });
            });
        }

        // 5) window resize 兜底：窗口大小变化时同步所有可见表（300ms 防抖）
        if (!XFAdmin._dtWinResizeBound) {
            XFAdmin._dtWinResizeBound = true;
            var winResizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(winResizeTimer);
                winResizeTimer = setTimeout(function () {
                    try {
                        DataTable.tables({ visible: true, api: true }).columns.adjust();
                    } catch (e) { /* noop */ }
                }, 300);
            });
        }

        // 每列筛选输入框
        if (config.columnFilters) {
            var headerRow = el.querySelector('thead tr');
            var filterRow = document.createElement('tr');
            filterRow.className = 'xf-filter-row';
            Array.prototype.forEach.call(headerRow.children, function (th, idx) {
                var td = document.createElement('td');
                var input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm';
                input.placeholder = '筛选 ' + th.textContent.trim();
                var timer = null;
                input.addEventListener('input', function () {
                    var v = this.value;
                    clearTimeout(timer);
                    timer = setTimeout(function () { table.column(idx).search(v).draw(); }, 300);
                });
                td.appendChild(input);
                filterRow.appendChild(td);
            });
            el.querySelector('thead').appendChild(filterRow);
        }

        // 过滤工具栏（filter_bar）：变更 => 拼查询参数 => 重载
        if (config.filterBar) {
            var bar = document.querySelector('form[data-xf-filter-for="' + el.id + '"]');
            if (bar) {
                var baseUrl = (typeof dt.ajax === 'string' ? dt.ajax : (dt.ajax && dt.ajax.url) || '').split('?')[0];
                // 列名（data）=> 列索引，供本地模式按过滤条件定位列
                var colIndexByName = {};
                try { table.columns().every(function () { var n = this.dataSrc(); if (typeof n === 'string') colIndexByName[n] = this.index(); }); } catch (e) { /* noop */ }
                var applyFilterValue = function (name, value) {
                    if (typeof name !== 'string' || !name || !value) return;
                    var ci = colIndexByName[name];
                    if (ci != null) table.column(ci).search(value);
                };
                // 多选/select2 控件：升级为 select2 交互（可搜索、标签式多选、中文提示）
                if (global.jQuery && global.jQuery.fn.select2) {
                    bar.querySelectorAll('select.xf-filter-s2, select.xf-filter[multiple]').forEach(function (sel) {
                        global.jQuery(sel).select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: sel.getAttribute('data-placeholder') || '全部',
                            language: {
                                noResults: function () { return '无匹配结果'; },
                                searching: function () { return '搜索中…'; },
                                removeAllItems: function () { return '清除所有'; }
                            }
                        });
                    });
                }
                // 收集单个控件的过滤值
                var controlValue = function (c) {
                    if (c.type === 'checkbox') return c.checked ? (c.value || '1') : '';
                    if (c.multiple) {
                        return Array.from(c.selectedOptions).map(function (o) { return o.value; })
                            .filter(function (x) { return x !== ''; }).join(',');
                    }
                    // color 控件：保持默认值时视为未过滤
                    if (c.type === 'color' && c.value === (c.defaultValue || '')) return '';
                    return c.value;
                };
                var eachFilterValue = function (cb) {
                    bar.querySelectorAll('.xf-filter').forEach(function (c) { cb(c.dataset.filter, controlValue(c)); });
                    bar.querySelectorAll('.xf-filter-radio').forEach(function (g) {
                        var activeBtn = g.querySelector('.active');
                        cb(g.dataset.filter, activeBtn ? activeBtn.dataset.value : '');
                    });
                    // 复选组：勾选值逗号连接
                    bar.querySelectorAll('.xf-filter-checks').forEach(function (g) {
                        var vs = Array.from(g.querySelectorAll('input:checked')).map(function (i) { return i.value; });
                        cb(g.dataset.filter, vs.join(','));
                    });
                };
                var reloadWithFilters = function () {
                    var qs = [];
                    eachFilterValue(function (name, fv) {
                        if (name && fv) qs.push(encodeURIComponent(name) + '=' + encodeURIComponent(fv));
                    });
                    if (baseUrl) {
                        table.ajax.url(baseUrl + (qs.length ? '?' + qs.join('&') : '')).load();
                    } else {
                        // 本地模式：按 filter 名映射到列，应用客户端搜索
                        table.columns().search('');
                        eachFilterValue(function (name, fv) { applyFilterValue(name, fv); });
                        table.draw();
                    }
                };
                // 交互模式：默认点击「搜索」按钮才发起过滤请求；filterAuto=true 时即改即查
                var auto = !!config.filterAuto;
                var filterTimer = null;
                if (auto) {
                    bar.querySelectorAll('.xf-filter').forEach(function (c) {
                        c.addEventListener('change', reloadWithFilters);
                        if (c.type === 'text' || c.type === 'search' || c.type === 'number') {
                            c.addEventListener('input', function () {
                                clearTimeout(filterTimer);
                                filterTimer = setTimeout(reloadWithFilters, 400);
                            });
                        }
                    });
                    if (global.jQuery && global.jQuery.fn.select2) {
                        global.jQuery(bar).on('change', 'select.xf-filter-s2, select.xf-filter[multiple]', reloadWithFilters);
                    }
                    bar.querySelectorAll('.xf-filter-checks input').forEach(function (i) { i.addEventListener('change', reloadWithFilters); });
                }
                // 回车 = 搜索（两种模式均支持）
                bar.querySelectorAll('.xf-filter').forEach(function (c) {
                    c.addEventListener('keyup', function (e) { if (e.key === 'Enter') reloadWithFilters(); });
                });
                bar.querySelectorAll('.xf-filter-radio').forEach(function (g) {
                    g.addEventListener('click', function (e) {
                        var btn = e.target.closest('[data-value]');
                        if (!btn) return;
                        g.querySelectorAll('.btn').forEach(function (b) { b.classList.remove('active'); });
                        btn.classList.add('active');
                        if (auto) reloadWithFilters();
                    });
                });
                // 搜索按钮（submit 亦触发，天然支持表单回车提交）
                var searchBtn = bar.querySelector('.xf-filter-search');
                if (searchBtn) searchBtn.addEventListener('click', function (e) { e.preventDefault(); reloadWithFilters(); });
                bar.addEventListener('submit', function (e) { e.preventDefault(); reloadWithFilters(); });
                var resetBtn = bar.querySelector('.xf-filter-reset');
                if (resetBtn) {
                    resetBtn.addEventListener('click', function () {
                        bar.querySelectorAll('.xf-filter').forEach(function (c) {
                            if (c.type === 'checkbox') { c.checked = false; return; }
                            if (c.multiple) {
                                Array.from(c.options).forEach(function (o) { o.selected = false; });
                                if (global.jQuery && global.jQuery.fn.select2) global.jQuery(c).trigger('change.select2');
                                return;
                            }
                            if (c.type === 'color') { c.value = c.defaultValue || '#000000'; return; }
                            c.value = '';
                            if (c.classList.contains('xf-filter-s2') && global.jQuery && global.jQuery.fn.select2) {
                                global.jQuery(c).val('').trigger('change.select2');
                            }
                        });
                        bar.querySelectorAll('.xf-filter-checks input').forEach(function (i) { i.checked = false; });
                        bar.querySelectorAll('.xf-filter-radio').forEach(function (g) {
                            g.querySelectorAll('.btn').forEach(function (b, i) { b.classList.toggle('active', i === 0); });
                        });
                        reloadWithFilters();
                    });
                }
            }
        }

        // ===== 交互增强：行明细展开（点击首列按钮展开行内详情） =====
        if (config.rowDetail) {
            XFAdmin.bindRowDetail(table, el, config.rowDetail);
        }
        // ===== 交互增强：批量选择 + 批量操作栏 =====
        if (config.bulk) {
            XFAdmin.bindBulk(table, el, config.bulk);
        }

        // 固定列（CSS sticky 实现）：config.fixedColumns = {left: N, right: M}
        if (config.fixedColumns && (config.fixedColumns.left || config.fixedColumns.right)) {
            var applySticky = function () {
                var wrapper = el.closest('.dt-container') || el.closest('.dataTables_wrapper') || el.parentElement;
                if (!wrapper) return;
                var tables = wrapper.querySelectorAll('table');
                var nLeft = config.fixedColumns.left || 0;
                var nRight = config.fixedColumns.right || 0;
                // 以主体表首行实测列宽，计算每个固定列的 sticky 偏移
                var bodyRow = el.querySelector('tbody tr');
                var cells = bodyRow ? Array.from(bodyRow.children) : [];
                if (!cells.length) return;
                var total = cells.length;
                var leftOffsets = [], rightOffsets = [];
                var acc = 0;
                for (var i = 0; i < nLeft && i < total; i++) { leftOffsets[i] = acc; acc += cells[i].getBoundingClientRect().width; }
                acc = 0;
                for (var j = 0; j < nRight && j < total; j++) { var ci = total - 1 - j; rightOffsets[ci] = acc; acc += cells[ci].getBoundingClientRect().width; }
                tables.forEach(function (t) {
                    t.querySelectorAll('tr').forEach(function (tr) {
                        Array.from(tr.children).forEach(function (cell, idx) {
                            cell.classList.remove('xf-dt-sticky', 'xf-dt-sticky-end');
                            cell.style.left = ''; cell.style.right = '';
                            if (idx < nLeft && leftOffsets[idx] != null) {
                                cell.classList.add('xf-dt-sticky');
                                cell.style.left = leftOffsets[idx] + 'px';
                            } else if (rightOffsets[idx] != null) {
                                cell.classList.add('xf-dt-sticky', 'xf-dt-sticky-end');
                                cell.style.right = rightOffsets[idx] + 'px';
                            }
                        });
                    });
                });
            };
            var stickyTimer = null;
            var scheduleSticky = function () { clearTimeout(stickyTimer); stickyTimer = setTimeout(applySticky, 50); };
            if (global.jQuery) {
                // init.dt 首次数据加载完成即触发；draw.dt 每次重绘；columns.adjusted.dt 列宽重算
                global.jQuery(el).on('init.dt draw.dt column-visibility.dt columns.adjusted.dt', scheduleSticky);
            } else {
                el.addEventListener('draw.dt', scheduleSticky);
                el.addEventListener('init.dt', scheduleSticky);
            }
            scheduleSticky();
            window.addEventListener('resize', scheduleSticky);
            // ResizeObserver 监听容器尺寸变化（侧边栏折叠等）
            var wrapper = el.closest('.dt-container') || el.closest('.dataTables_wrapper') || el.parentElement;
            if (wrapper && typeof ResizeObserver !== 'undefined') {
                var ro = new ResizeObserver(function () { scheduleSticky(); });
                ro.observe(wrapper);
            }
        }
        return table;
    });

    /* 获取已初始化的 DataTable 实例：XFAdmin.table('dt-users') */
    XFAdmin.table = function (idOrEl) {
        var el = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
        return el ? el.__xfTable : null;
    };

    /* 重载表格数据（服务端模式重新请求，本地模式重绘）；url 可选（切换数据源） */
    XFAdmin.reloadTable = function (idOrEl, url) {
        var table = XFAdmin.table(idOrEl);
        if (!table) return;
        if (table.ajax && table.ajax.url()) {
            if (url) table.ajax.url(url);
            table.ajax.reload(null, false);
        } else {
            table.draw(false);
        }
    };

    /* 弹窗关闭事件全局桥接：当某弹窗（编辑/新增/详情）由 DataTable 行操作打开且
       内部表单已成功保存后关闭时，派发的 xf:dialog-closed 事件在此被任何表格页面接收。
       页面可监听该事件自行决定是否刷新（默认仅在 saved=true 且带回 tableId 时刷新对应表格）。 */
    document.addEventListener('xf:dialog-closed', function (e) {
        var d = e.detail || {};
        if (d.tableId) {
            var el = document.getElementById(d.tableId);
            if (el && el.__xfTable) {
                // 保存成功 -> 刷新；其余关闭 -> 不刷新（由弹窗逻辑已保证 only-saved 才置位）
                if (d.saved) XFAdmin.reloadTable(el);
            }
        }
    });

    /* ------------------------------------------------------------------
     * 表格单元格交互（全局事件委托，一次绑定）：
     * - [data-xf-copy]           点击复制
     * - .xf-cell-switch          状态开关（可选 AJAX 提交）
     * - .xf-cell-input           单元格输入框（change 时可选 AJAX 提交）
     * - [data-xf-act]            行操作按钮（ajax / view / copy-row / 自定义事件）
     * ---------------------------------------------------------------- */
    function rowOf(target) {
        var tr = target.closest('tr');
        var tableEl = target.closest('table');
        var table = tableEl && tableEl.__xfTable;
        if (table && tr) {
            try { return table.row(tr).data() || null; } catch (e) { return null; }
        }
        return null;
    }

    document.addEventListener('click', function (e) {
        // 点击复制
        var copyEl = e.target.closest('[data-xf-copy]');
        if (copyEl) {
            XFAdmin.copyText(copyEl.getAttribute('data-xf-copy'));
            return;
        }
        // 单元格时间线「查看全部」
        var tlBtn = e.target.closest('.xf-cell-tl-more');
        if (tlBtn) {
            var events = [];
            try { events = JSON.parse(tlBtn.getAttribute('data-xf-tl') || '[]'); } catch (tlErr) { events = []; }
            XFAdmin.dialog({
                title: tlBtn.getAttribute('data-xf-tl-title') || '时间线',
                size: 'lg',
                body: XFAdmin.timelineHtml(events)
            });
            return;
        }
        // 按钮切换（toggle 渲染器）
        var tgl = e.target.closest('.xf-cell-toggle');
        if (tgl) {
            var next = tgl.getAttribute('data-xf-state') !== '1';
            var value = next ? tgl.getAttribute('data-xf-on') : tgl.getAttribute('data-xf-off');
            var applyState = function (state) {
                tgl.setAttribute('data-xf-state', state ? '1' : '0');
                tgl.className = 'btn btn-sm xf-cell-toggle ' +
                    (state ? tgl.getAttribute('data-xf-on-class') : tgl.getAttribute('data-xf-off-class'));
                tgl.innerHTML = '<i class="ti ti-' + (state ? 'check' : 'ban') + ' me-1"></i>' +
                    escapeHtml(state ? tgl.getAttribute('data-xf-on-label') : tgl.getAttribute('data-xf-off-label'));
            };
            document.dispatchEvent(new CustomEvent('xf:toggle', {
                detail: {
                    el: tgl, value: value,
                    field: tgl.getAttribute('data-xf-field'),
                    id: tgl.getAttribute('data-xf-id'),
                    row: rowOf(tgl)
                }
            }));
            var tglUrl = tgl.getAttribute('data-xf-url');
            if (!tglUrl) { applyState(next); return; }
            tgl.disabled = true;
            var payload = {};
            payload[tgl.getAttribute('data-xf-field') || 'status'] = value;
            payload.id = tgl.getAttribute('data-xf-id');
            XFAdmin.request(tglUrl, { method: 'POST', data: payload }).then(function (res) {
                tgl.disabled = false;
                if (res.ok) {
                    applyState(next);
                    XFAdmin.toast({ body: (res.data && res.data.message) || '状态已更新', variant: 'success' });
                }
            });
            return;
        }
        // 二维码放大查看
        var qrEl = e.target.closest('.xf-cell-qr');
        if (qrEl) {
            var qrtxt = qrEl.getAttribute('data-xf-qr');
            if (qrtxt && typeof qrcode === 'function') {
                try {
                    var big = qrcode(0, 'M');
                    big.addData(qrtxt);
                    big.make();
                    var bsvg = big.createSvgTag(6, 8, '', '')
                        .replace('fill="white"', 'fill="#ffffff"')
                        .replace('fill="black"', 'fill="#000000"');
                    var dataUrl = big.createDataURL(6, 8);
                    var actions = '<a class="btn btn-sm btn-primary" download="qrcode.gif" href="' + dataUrl + '">下载图片</a>';
                    if (/^https?:/i.test(qrtxt)) {
                        actions = '<a class="btn btn-sm btn-primary" href="' + encodeURI(qrtxt) + '" target="_blank" rel="noopener">打开链接</a> ' + actions;
                    }
                    XFAdmin.dialog({
                        title: '二维码', size: 'sm',
                        body: '<div class="text-center p-3" style="max-width:320px;margin:auto">' + bsvg + '</div>'
                            + '<div class="text-center pb-3">' + actions + '</div>',
                    });
                } catch (e) { XFAdmin.toast({ body: '二维码生成失败', variant: 'danger' }); }
            }
            return;
        }
        // 行操作按钮
        var actEl = e.target.closest('[data-xf-act]');
        if (!actEl) return;
        var act = actEl.getAttribute('data-xf-act');
        var row = rowOf(actEl);
        var tableEl = actEl.closest('table');

        // 自定义事件：任何 action 均派发 xf:action，供业务侧监听扩展
        var detail = { action: act, row: row, el: actEl, table: tableEl && tableEl.__xfTable };
        document.dispatchEvent(new CustomEvent('xf:action', { detail: detail }));
        if (actEl.getAttribute('data-xf-event')) {
            document.dispatchEvent(new CustomEvent(actEl.getAttribute('data-xf-event'), { detail: detail }));
            return;
        }

        if (act === 'copy-row') {
            XFAdmin.copyText(JSON.stringify(row || {}, null, 2), '行数据已复制');
            return;
        }
        if (act === 'view') {
            var viewCfg = null;
            try { viewCfg = JSON.parse(actEl.getAttribute('data-xf-view') || 'null'); } catch (vErr) { viewCfg = null; }
            XFAdmin.viewRow(row, viewCfg || undefined, tableEl);
            return;
        }
        if (act === 'edit') {
            var editCfg;
            try { editCfg = JSON.parse(actEl.getAttribute('data-xf-edit') || '{}'); } catch (err) { editCfg = {}; }
            XFAdmin.editRow(row, editCfg, tableEl);
            return;
        }
        // 删除闭环：确认 -> 请求后端（DELETE）-> 刷新表格；本地数据源无 URL 时直接移除行
        if (act === 'delete') {
            var delUrl = actEl.getAttribute('data-xf-url');
            var delMsg = actEl.getAttribute('data-xf-confirm') || '确定删除该记录？此操作不可恢复。';
            var doDelete = function () {
                var tr = actEl.closest('tr');
                var dtIns = tableEl && tableEl.__xfTable;
                var removeLocal = function () {
                    if (dtIns && tr) { try { dtIns.row(tr).remove().draw(false); } catch (rmErr) { /* noop */ } }
                };
                if (!delUrl) {
                    removeLocal();
                    XFAdmin.toast({ body: '删除成功', variant: 'success' });
                    return;
                }
                actEl.disabled = true;
                XFAdmin.request(delUrl, { method: actEl.getAttribute('data-xf-method') || 'DELETE' }).then(function (res) {
                    actEl.disabled = false;
                    if (res.ok) {
                        XFAdmin.toast({ body: (res.data && res.data.message) || '删除成功', variant: 'success' });
                        if (dtIns && dtIns.ajax && dtIns.ajax.url()) XFAdmin.reloadTable(tableEl);
                        else removeLocal();
                    }
                });
            };
            // 气泡卡片（Popover）确认：在被点击按钮旁显示取消/确定，避免全屏遮罩
            if (actEl.getAttribute('data-xf-confirm-popover')) {
                XFAdmin.popoverConfirm(actEl, delMsg, doDelete);
            } else {
                XFAdmin.confirm({ title: '删除确认', text: delMsg, icon: 'warning' }, doDelete);
            }
            return;
        }
        if (act === 'ajax') {
            var url = actEl.getAttribute('data-xf-url');
            if (!url) return;
            var doIt = function () {
                actEl.disabled = true;
                XFAdmin.request(url, { method: actEl.getAttribute('data-xf-method') || 'POST' }).then(function (res) {
                    actEl.disabled = false;
                    if (res.ok) {
                        XFAdmin.toast({ body: (res.data && res.data.message) || '操作成功', variant: 'success' });
                        if (actEl.getAttribute('data-xf-reload') && tableEl) XFAdmin.reloadTable(tableEl);
                    }
                });
            };
            var confirmMsg = actEl.getAttribute('data-xf-confirm');
            if (confirmMsg) { XFAdmin.confirm(confirmMsg, doIt); } else { doIt(); }
        }
        if (act === 'download') {
            var dlUrl = actEl.getAttribute('data-xf-url');
            if (!dlUrl) return;
            var a = document.createElement('a');
            a.href = dlUrl;
            if (!/^https?:/i.test(dlUrl)) a.setAttribute('download', ''); // 同源强制触发下载
            a.target = '_blank';
            a.rel = 'noopener';
            document.body.appendChild(a);
            a.click();
            a.remove();
            return;
        }
        if (act === 'print') {
            var printUrl = actEl.getAttribute('data-xf-url');
            var printTitle = actEl.getAttribute('data-xf-title') || (row && row[actEl.getAttribute('data-xf-name') || 'name']) || '';
            if (printUrl) {
                // 带打印框架的弹窗（iframe 内触发 window.print）
                XFAdmin.pageDialog(printUrl, { title: printTitle || '打印', size: 'lg', frame: true, onMount: function (frame) {
                    try { frame.contentWindow.focus(); frame.contentWindow.print(); } catch (e) { /* 跨域时由子页自行打印 */ }
                } });
                return;
            }
            // 无 url：以弹窗形式展示当前行数据并调用浏览器打印
            var html = '<table class="table table-sm"><tbody>';
            Object.keys(row || {}).forEach(function (k) {
                if (typeof row[k] === 'object') return;
                html += '<tr><th class="text-nowrap">' + escapeHtml(k) + '</th><td>' + escapeHtml(String(row[k])) + '</td></tr>';
            });
            html += '</tbody></table>';
            XFAdmin.pageDialog(html, { title: '打印预览 · ' + printTitle, size: 'md' }).then(function (dlg) {
                try { (dlg.el.querySelector('.modal-body') || window).ownerDocument.defaultView.print(); } catch (e) { window.print(); }
            });
            return;
        }
        if (act === 'share') {
            // 复制共享链接（data-xf-url 或当前行拼出的 url），失败回退为提示
            var shareUrl = actEl.getAttribute('data-xf-url') || (row ? location.origin + location.pathname + '#row-' + (row.id || '') : '');
            var shareMsg = actEl.getAttribute('data-xf-title') || '链接已复制，去分享吧';
            if (navigator.share && !actEl.getAttribute('data-xf-noshare')) {
                try { navigator.share({ title: document.title, url: shareUrl }).catch(function () {}); return; } catch (e) { /* 降级复制 */ }
            }
            XFAdmin.copyText(shareUrl, shareMsg);
            return;
        }
    });

    /**
     * 确认弹窗（全包唯一实现，返回 Promise<boolean>，两种调用方式兼容）：
     *   XFAdmin.confirm('确定删除？', onOk, onCancel)
     *   XFAdmin.confirm({ title, text, icon, confirmText, cancelText }).then(function (ok) {...})
    /**
     * 气泡卡片（Popover）确认：在被点击锚点元素旁显示卡片，内含确认文案、取消/确定按钮。
     * 优先用 Bootstrap Popover（依赖 Popper），缺失时降级为 XFAdmin.confirm。
     * @param {HTMLElement} anchor 触发元素（删除按钮）
     * @param {string} msg 提示文案
     * @param {Function} onConfirm 用户点击“确定”后的回调
     */
    XFAdmin.popoverConfirm = function (anchor, msg, onConfirm) {
        if (!global.bootstrap || !global.bootstrap.Popover) {
            XFAdmin.confirm({ text: msg, icon: 'warning' }, onConfirm);
            return;
        }
        var confirmed = false;
        var card = document.createElement('div');
        card.className = 'xf-popover-confirm card border-0 shadow';
        card.innerHTML =
            '<div class="card-body p-3" style="width:240px">' +
            '<div class="d-flex align-items-start gap-2 mb-3">' +
            '<i class="ti ti-alert-triangle text-warning fs-4 flex-shrink-0 mt-1"></i>' +
            '<div class="xf-popover-msg small"></div>' +
            '</div>' +
            '<div class="d-flex justify-content-end gap-2">' +
            '<button type="button" class="btn btn-sm btn-light xf-pop-cancel">取消</button>' +
            '<button type="button" class="btn btn-sm btn-danger xf-pop-ok">确定</button>' +
            '</div></div>';
        card.querySelector('.xf-popover-msg').textContent = msg;

        var pop = new global.bootstrap.Popover(anchor, {
            content: card,
            html: true,
            sanitize: false,
            placement: 'left',
            trigger: 'manual',
            container: document.body,
            customClass: 'xf-popover-confirm-wrap'
        });
        var clean = function () { try { pop && pop.dispose && pop.dispose(); } catch (e) {} };
        pop.show();
        // 绑定卡片内按钮
        card.querySelector('.xf-pop-cancel').addEventListener('click', function () { clean(); });
        card.querySelector('.xf-pop-ok').addEventListener('click', function () {
            if (confirmed) return; confirmed = true;
            clean();
            try { onConfirm && onConfirm(); } catch (err) { console.error('[XFAdmin] popoverConfirm', err); }
        });
        // 点击卡片外区域关闭
        setTimeout(function () {
            var onDoc = function (e) {
                if (card.contains(e.target)) return;
                if (anchor.contains(e.target)) return;
                clean();
                document.removeEventListener('click', onDoc, true);
            };
            document.addEventListener('click', onDoc, true);
        }, 0);
    };

    /**
     * 通用确认对话框。
     * 优先 SweetAlert2，其次 Bootstrap Modal，最后原生 confirm。
     */
    XFAdmin.confirm = function (message, onOk, onCancel) {
        var opts = (message && typeof message === 'object') ? Object.assign({}, message) : { text: str(message) };
        if (typeof onOk === 'function' && !opts.onOk) opts.onOk = onOk;
        if (typeof onCancel === 'function' && !opts.onCancel) opts.onCancel = onCancel;
        var settled = false;
        var finish;
        var promise = new Promise(function (resolve) {
            finish = function (ok) {
                if (settled) return;
                settled = true;
                resolve(!!ok);
                try { if (ok) { opts.onOk && opts.onOk(); } else { opts.onCancel && opts.onCancel(); } }
                catch (err) { console.error('[XFAdmin] confirm callback', err); }
            };
        });

        if (global.Swal) {
            global.Swal.fire({
                title: opts.title || '确认操作',
                text: opts.text || '',
                icon: opts.icon || 'question',
                showCancelButton: true,
                confirmButtonText: opts.confirmText || '确定',
                cancelButtonText: opts.cancelText || '取消',
                customClass: { confirmButton: 'btn btn-primary me-2 mt-2', cancelButton: 'btn btn-light mt-2' },
                buttonsStyling: false
            }).then(function (r) { finish(r.isConfirmed); });
            return promise;
        }
        if (global.bootstrap && global.bootstrap.Modal) {
            var modalEl = document.getElementById('xf-confirm-modal');
            if (!modalEl) {
                modalEl = document.createElement('div');
                modalEl.id = 'xf-confirm-modal';
                modalEl.className = 'modal fade';
                modalEl.tabIndex = -1;
                modalEl.innerHTML = '<div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">' +
                    '<div class="modal-body text-center pt-4"><i class="ti ti-alert-circle text-warning" style="font-size:2.5rem"></i>' +
                    '<p class="mt-2 mb-0 xf-confirm-msg"></p></div>' +
                    '<div class="modal-footer border-0 justify-content-center pb-4">' +
                    '<button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">取消</button>' +
                    '<button type="button" class="btn btn-sm btn-danger xf-confirm-ok">确定</button></div></div></div>';
                document.body.appendChild(modalEl);
            }
            modalEl.querySelector('.xf-confirm-msg').textContent = opts.text || opts.title || '确认操作？';
            var modal = global.bootstrap.Modal.getOrCreateInstance(modalEl);
            var okBtn = modalEl.querySelector('.xf-confirm-ok');
            var newOk = okBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(newOk, okBtn);
            newOk.addEventListener('click', function () {
                modal.hide();
                finish(true);
            });
            var onHidden = function () {
                modalEl.removeEventListener('hidden.bs.modal', onHidden);
                finish(false); // 未点确定即关闭 => 取消
            };
            modalEl.addEventListener('hidden.bs.modal', onHidden);
            modal.show();
            return promise;
        }
        finish(window.confirm(opts.text || opts.title || '确认操作？'));
        return promise;
    };

    /* ------------------------------------------------------------------
     * 行详情弹窗（多布局详情引擎）
     *   XFAdmin.viewRow(row)                        默认键值对布局（自动复用表格列头中文名与列渲染器）
     *   XFAdmin.viewRow(row, '标题')                向后兼容：字符串标题
     *   XFAdmin.viewRow(row, cfg, tableEl)          个性化配置：
     *     cfg.title      标题模板，支持 {field} 占位（如 '客户详情 - {name}'）
     *     cfg.size       弹窗尺寸 sm|lg|xl（默认 lg）
     *     cfg.layout     kv | profile | tabs | sections | template（省略时按 sections/template 自动推断）
     *     cfg.labels     {field: '中文名'} 补充/覆盖列头名
     *     cfg.fields     kv 布局字段白名单及顺序
     *     cfg.exclude    排除字段
     *     cfg.cols       kv 布局列数 1|2
     *     cfg.header     profile 头部 {avatar: 'avatar字段', title: '{name}', sub: '{email}', badge: {field, map}}
     *     cfg.template   layout=template 时的 HTML 模板（{field} 占位，值自动转义）
     *     cfg.sections   分区数组 [{title, icon, type, fields|field|template, columns, cols}]
     *                    type: kv | table | timeline | stats | tags | progress | images | html
     *     cfg.renderers  {field: {type: 'money', ...}} 指定字段用 cellRenderers 渲染
     *     cfg.ajax       详情接口 URL 模板（如 '/api/users/{id}'），打开时拉取并合并到行数据
     * ---------------------------------------------------------------- */

    /* 时间线 HTML（详情弹窗 / 单元格「查看全部」共用） */
    XFAdmin.timelineHtml = function (list) {
        if (!Array.isArray(list) || !list.length) return '<div class="text-muted">暂无记录</div>';
        var html = '<div class="xf-view-tl">';
        list.forEach(function (ev) {
            var t = typeof ev === 'string' ? { title: ev } : (ev || {});
            html += '<div class="xf-view-tl-item"><span class="xf-view-tl-dot bg-' + escapeHtml(t.color || 'primary') + '"></span>' +
                '<div class="xf-view-tl-body">' +
                (t.time ? '<div class="text-muted fs-12">' + escapeHtml(t.time) + '</div>' : '') +
                '<div class="fw-medium">' + (t.icon ? '<i class="' + escapeHtml(t.icon) + ' me-1"></i>' : '') + escapeHtml(t.title || '') + '</div>' +
                (t.text ? '<div class="text-muted fs-13">' + escapeHtml(t.text) + '</div>' : '') +
                (t.user ? '<div class="text-muted fs-12 mt-1"><i class="ti ti-user me-1"></i>' + escapeHtml(t.user) + '</div>' : '') +
                '</div></div>';
        });
        return html + '</div>';
    };

    XFAdmin.viewRow = function (row, cfg, tableEl) {
        row = row || {};
        if (typeof cfg === 'string') cfg = { title: cfg };
        cfg = cfg || {};
        var meta = (tableEl && tableEl.__xfColMeta) || {};

        function labelOf(k) {
            return (cfg.labels && cfg.labels[k]) || (meta[k] && meta[k].label) || k;
        }

        function detailVal(k, data) {
            var v = data[k];
            var r = cfg.renderers && cfg.renderers[k];
            if (r) {
                var rc = typeof r === 'string' ? { type: r } : r;
                var fn = XFAdmin.cellRenderers[rc.type];
                if (fn) { try { return fn(v, data, rc, null); } catch (re) { /* fallthrough */ } }
            }
            if (meta[k] && typeof meta[k].render === 'function') {
                try { return meta[k].render(v, 'display', data, null); } catch (me) { /* fallthrough */ }
            }
            if (v == null || v === '') return '<span class="text-muted">-</span>';
            if (typeof v === 'object') return '<pre class="mb-0 fs-12 bg-light rounded p-2 text-break">' + escapeHtml(JSON.stringify(v, null, 2)) + '</pre>';
            return escapeHtml(v);
        }

        function pickKeys(data, fields) {
            var keys = fields && fields.length ? fields.slice() : Object.keys(data);
            var exclude = cfg.exclude || [];
            return keys.filter(function (k) {
                if (!k || k.charAt(0) === '_') return false;
                if (exclude.indexOf(k) !== -1) return false;
                return typeof data[k] !== 'function';
            });
        }

        function kvHtml(keys, data, cols) {
            var colCls = cols === 2 ? ' col-md-6' : ' col-12';
            var html = '<div class="row g-0 xf-view-kv">';
            keys.forEach(function (k) {
                html += '<div class="col-12' + (cols === 2 ? ' col-md-6' : '') + '"><div class="xf-view-kv-item">' +
                    '<div class="xf-view-kv-label">' + escapeHtml(labelOf(k)) + '</div>' +
                    '<div class="xf-view-kv-value">' + detailVal(k, data) + '</div></div></div>';
            });
            void colCls;
            return html + '</div>';
        }

        function statsHtml(fields, data) {
            var items = (fields || []).map(function (f) { return typeof f === 'string' ? { field: f } : f; });
            var html = '<div class="row g-2 text-center">';
            items.forEach(function (it) {
                var v = data[it.field];
                html += '<div class="col"><div class="xf-view-stat bg-' + escapeHtml(it.color || 'primary') + '-subtle">' +
                    (it.icon ? '<i class="' + escapeHtml(it.icon) + ' fs-4 text-' + escapeHtml(it.color || 'primary') + '"></i>' : '') +
                    '<div class="fs-4 fw-bold text-' + escapeHtml(it.color || 'primary') + '">' +
                    escapeHtml(it.prefix || '') + escapeHtml(v == null ? '-' : v) + escapeHtml(it.suffix || '') + '</div>' +
                    '<div class="text-muted fs-12">' + escapeHtml(it.label || labelOf(it.field)) + '</div></div></div>';
            });
            return html + '</div>';
        }

        function tableHtml(sec, data) {
            var rows = data[sec.field];
            if (!Array.isArray(rows) || !rows.length) return '<div class="text-muted">暂无数据</div>';
            var columns = sec.columns;
            if (!columns) {
                columns = {};
                Object.keys(rows[0] || {}).forEach(function (k) { columns[k] = k; });
            }
            var keys = Object.keys(columns);
            var html = '<div class="table-responsive"><table class="table table-sm table-striped mb-0"><thead><tr>';
            keys.forEach(function (k) { html += '<th class="text-nowrap">' + escapeHtml(columns[k]) + '</th>'; });
            html += '</tr></thead><tbody>';
            rows.forEach(function (r) {
                html += '<tr>';
                keys.forEach(function (k) {
                    var v = r && r[k];
                    html += '<td>' + (v == null ? '<span class="text-muted">-</span>' : escapeHtml(typeof v === 'object' ? JSON.stringify(v) : v)) + '</td>';
                });
                html += '</tr>';
            });
            return html + '</tbody></table></div>';
        }

        function progressHtml(fields, data) {
            var items = (fields || []).map(function (f) { return typeof f === 'string' ? { field: f } : f; });
            var html = '';
            items.forEach(function (it) {
                var n = Math.max(0, Math.min(100, parseFloat(data[it.field]) || 0));
                var color = it.color || (n >= 80 ? 'success' : n >= 40 ? 'primary' : 'warning');
                html += '<div class="mb-2"><div class="d-flex justify-content-between fs-13 mb-1">' +
                    '<span>' + escapeHtml(it.label || labelOf(it.field)) + '</span><span class="text-muted">' + n + '%</span></div>' +
                    '<div class="progress" style="height:8px"><div class="progress-bar bg-' + escapeHtml(color) + '" style="width:' + n + '%"></div></div></div>';
            });
            return html || '<div class="text-muted">暂无数据</div>';
        }

        function imagesHtml(sec, data) {
            var list = data[sec.field];
            if (!Array.isArray(list) || !list.length) return '<div class="text-muted">暂无图片</div>';
            var size = sec.size || 72;
            return '<div class="d-flex flex-wrap gap-2">' + list.map(function (src) {
                return '<img src="' + escapeHtml(src) + '" alt="" class="rounded border" style="width:' + size + 'px;height:' + size + 'px;object-fit:cover">';
            }).join('') + '</div>';
        }

        function sectionHtml(sec, data) {
            var type = sec.type || 'kv';
            if (type === 'kv') return kvHtml(pickKeys(data, sec.fields), data, sec.cols || 2);
            if (type === 'timeline') return XFAdmin.timelineHtml(Array.isArray(data[sec.field]) ? data[sec.field] : (sec.items || []));
            if (type === 'table') return tableHtml(sec, data);
            if (type === 'stats') return statsHtml(sec.fields, data);
            if (type === 'tags') return XFAdmin.cellRenderers.tags(data[sec.field], data, sec, null);
            if (type === 'progress') return progressHtml(sec.fields, data);
            if (type === 'images') return imagesHtml(sec, data);
            if (type === 'html' || type === 'template') return XFAdmin.tpl(sec.template || '', data);
            return '';
        }

        function profileHeaderHtml(h, data) {
            h = h || {};
            var av = h.avatar ? str(data[h.avatar]) : '';
            var title = h.title ? tplRaw(h.title, data) : str(data.name || data.title || data.id || '详情');
            var sub = h.sub ? tplRaw(h.sub, data) : '';
            var badge = '';
            if (h.badge && h.badge.field) {
                var bv = data[h.badge.field];
                var bm = (h.badge.map || {})[bv];
                var bc = typeof bm === 'string' ? bm : (bm && bm.color) || 'secondary';
                var bl = (bm && bm.label != null) ? bm.label : bv;
                badge = ' <span class="badge bg-' + escapeHtml(bc) + '-subtle text-' + escapeHtml(bc) + ' align-middle">' + escapeHtml(bl == null ? '' : bl) + '</span>';
            }
            var imgHtml = av
                ? '<img src="' + escapeHtml(av) + '" class="rounded-circle flex-shrink-0" style="width:56px;height:56px;object-fit:cover" alt="">'
                : '<span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width:56px;height:56px">' +
                  escapeHtml(str(title).charAt(0).toUpperCase()) + '</span>';
            return '<div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom xf-view-profile">' + imgHtml +
                '<div class="min-w-0"><h5 class="mb-1 text-truncate">' + escapeHtml(title) + badge + '</h5>' +
                (sub ? '<div class="text-muted fs-13 text-truncate">' + escapeHtml(sub) + '</div>' : '') + '</div></div>';
        }

        function buildBody(data) {
            var layout = cfg.layout || (cfg.sections ? 'sections' : (cfg.template ? 'template' : 'kv'));
            var html = '';
            if (cfg.header || layout === 'profile') html += profileHeaderHtml(cfg.header, data);
            if (layout === 'template') return html + XFAdmin.tpl(cfg.template || '', data);
            if (cfg.sections && layout === 'tabs') {
                var uid = 'xfvt' + Math.random().toString(36).slice(2, 8);
                var nav = '<ul class="nav nav-tabs nav-bordered mb-3">';
                var panes = '<div class="tab-content">';
                cfg.sections.forEach(function (sec, i) {
                    nav += '<li class="nav-item"><button type="button" class="nav-link' + (i ? '' : ' active') + '" data-bs-toggle="tab" data-bs-target="#' + uid + '-' + i + '">' +
                        (sec.icon ? '<i class="' + escapeHtml(sec.icon) + ' me-1"></i>' : '') + escapeHtml(sec.title || ('标签 ' + (i + 1))) + '</button></li>';
                    panes += '<div class="tab-pane fade' + (i ? '' : ' show active') + '" id="' + uid + '-' + i + '">' + sectionHtml(sec, data) + '</div>';
                });
                return html + nav + '</ul>' + panes + '</div>';
            }
            if (cfg.sections) {
                cfg.sections.forEach(function (sec) {
                    html += '<div class="xf-view-section">' +
                        (sec.title ? '<h6 class="xf-view-section-title">' + (sec.icon ? '<i class="' + escapeHtml(sec.icon) + ' me-1"></i>' : '') + escapeHtml(sec.title) + '</h6>' : '') +
                        sectionHtml(sec, data) + '</div>';
                });
                return html;
            }
            return html + kvHtml(pickKeys(data, cfg.fields), data, cfg.cols || (layout === 'profile' ? 2 : 1));
        }

        var open = function (data) {
            XFAdmin.dialog({
                title: cfg.title ? tplRaw(cfg.title, data) : '详细信息',
                size: cfg.size || 'lg',
                body: buildBody(data)
            });
        };

        if (cfg.ajax) {
            XFAdmin.request(tplRaw(cfg.ajax, row)).then(function (res) {
                var extra = res.ok ? (res.data && (res.data.data || res.data)) : null;
                if (extra && typeof extra === 'object' && !Array.isArray(extra)) {
                    open(Object.assign({}, row, extra));
                } else {
                    open(row);
                }
            });
            return;
        }
        open(row);
    };

    /* ------------------------------------------------------------------
     * 行明细展开 XFAdmin.bindRowDetail(table, el, cfg)
     *   table  DataTables 实例
     *   el     表格 DOM 元素
     *   cfg    true 或 { columns: [字段列表] }（仅展示指定字段）
     * 点击首列「明细」按钮，用 row.child() 展开该行全部字段的键值详情。
     * ------------------------------------------------------------------ */
    XFAdmin.bindRowDetail = function (table, el, cfg) {
        var $el = global.jQuery ? global.jQuery(el) : null;
        if (! $el) return;

        // 为每一行的明细列注入展开/收起按钮
        function fill() {
            table.rows().every(function () {
                var tr = this.node();
                var td = $el.find(tr).children('td.xf-dt-detail-col');
                if (td.length && ! td.data('xfFilled')) {
                    td.addClass('xf-dt-detail-cell');
                    td.html('<button type="button" class="btn btn-sm btn-ghost text-secondary xf-dt-detail-toggle" aria-label="展开/收起详情">' +
                        '<i class="ti ti-chevron-right"></i></button>');
                    td.data('xfFilled', 1);
                }
            });
        }

        // 构建行详情 HTML（复用表头标题 + 当前行数据）
        function detailHtml(rowData) {
            var initCols = (table.settings().init() && table.settings().init().columns) || [];
            var fields = cfg && cfg.columns && cfg.columns.length ? cfg.columns : null;
            var html = '<div class="xf-dt-detail-wrap p-3 bg-light-subtle">';
            html += '<dl class="row g-2 mb-0 xf-dt-detail-list">';
            var shown = 0;
            for (var i = 0; i < initCols.length; i++) {
                var c = initCols[i];
                if (c.data == null) continue;                       // 跳过明细/选择等辅助列
                if (fields && fields.indexOf(c.data) === -1) continue;
                var v = rowData[c.data];
                if (v == null) v = '';
                if (typeof v === 'object') v = JSON.stringify(v);
                html += '<dt class="col-5 col-md-3 text-muted small mb-1">' + escapeHtml(c.title || c.data) + '</dt>' +
                        '<dd class="col-7 col-md-9 small mb-1 text-break">' + escapeHtml(String(v)) + '</dd>';
                shown++;
            }
            if (shown === 0) {
                html += '<dt class="col-12 text-muted small">无可展示字段</dt>';
            }
            return html + '</dl></div>';
        }

        fill();
        $el.on('click', 'button.xf-dt-detail-toggle', function (e) {
            e.preventDefault();
            var tr = global.jQuery(this).closest('tr');
            var row = table.row(tr);
            var icon = global.jQuery(this).find('i');
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('xf-dt-detail-open');
                icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
            } else {
                row.child(detailHtml(row.data())).show();
                tr.addClass('xf-dt-detail-open');
                icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
            }
        });
        // 重绘后重新注入按钮（DataTables 重排行节点）
        $el.on('draw.dt', function () { fill(); });
    };

    /* ------------------------------------------------------------------
     * 批量操作 XFAdmin.bindBulk(table, el, cfg)
     *   cfg = { checkbox:true, actions:[{label,icon,class,url,method,confirm,reload}] }
     * 提供行复选框 + 表头全选 + 批量操作栏（选中后显示），提交时 {ids} 占位
     * 自动替换为逗号分隔的选中行 id（取自行数据的 id 字段）。
     * ------------------------------------------------------------------ */
    XFAdmin.bindBulk = function (table, el, cfg) {
        var $el = global.jQuery ? global.jQuery(el) : null;
        if (! $el) return;
        var $bar = global.jQuery('.xf-dt-bulk[data-dt="' + el.id + '"]');

        // 注入行复选框
        function fillRows() {
            table.rows().every(function () {
                var tr = this.node();
                var td = $el.find(tr).children('td.xf-dt-select-col');
                if (td.length && ! td.data('xfFilled')) {
                    td.addClass('xf-dt-select-cell');
                    td.html('<input type="checkbox" class="form-check-input xf-dt-row-check" aria-label="选择该行">');
                    td.data('xfFilled', 1);
                }
            });
        }
        // 注入表头全选框
        function fillHead() {
            var th = $el.find('thead th.xf-dt-select-col');
            if (th.length && ! th.data('xfFilled')) {
                th.html('<input type="checkbox" class="form-check-input xf-dt-select-all" aria-label="全选本页">');
                th.data('xfFilled', 1);
            }
        }
        // 收集选中行 id（取自行数据 id 字段）
        function selectedIds() {
            var ids = [];
            $el.find('tbody .xf-dt-row-check:checked').each(function () {
                var rd = table.row(global.jQuery(this).closest('tr')).data();
                if (rd && rd.id != null) ids.push(rd.id);
            });
            return ids;
        }
        // 刷新操作栏（选中数量 + 全选态）
        function refreshBar() {
            var checks = $el.find('tbody .xf-dt-row-check');
            var n = checks.filter(':checked').length;
            if ($bar.length) {
                $bar.toggleClass('d-none', n === 0);
                $bar.find('.xf-dt-bulk-count b').text(n);
            }
            var $all = $el.find('thead .xf-dt-select-all');
            if ($all.length) {
                $all.prop('checked', checks.length > 0 && n === checks.length);
                $all.prop('indeterminate', n > 0 && n < checks.length);
            }
        }

        fillRows();
        fillHead();

        $el.on('change', 'tbody .xf-dt-row-check', refreshBar);
        $el.on('change', 'thead .xf-dt-select-all', function () {
            var chk = this.checked;
            $el.find('tbody .xf-dt-row-check').prop('checked', chk);
            refreshBar();
        });
        $el.on('draw.dt', function () { fillRows(); fillHead(); refreshBar(); });
        refreshBar();

        if ($bar.length) {
            $bar.on('click', '[data-xf-bulk-action]', function () {
                // 注意：批量按钮位于表格外部的操作栏，须直接包装 this（$el.find(this) 会得到空集）
                var $btn = global.jQuery(this);
                var ids = selectedIds();
                if (! ids.length) {
                    XFAdmin.toast({ body: '请先选择要操作的数据', variant: 'warning' });
                    return;
                }
                var url = String($btn.attr('data-url') || '').replace(/\{ids\}/g, ids.join(','));
                var method = String($btn.attr('data-method') || 'POST').toUpperCase();
                var confirmMsg = $btn.attr('data-confirm');
                // data-reload="0" 表示成功后仅本地重绘（attr 返回字符串，避免 .data() 的数字 0 判定歧义）
                var reload = $btn.attr('data-reload') !== '0';
                var run = function () {
                    XFAdmin.request(url, { method: method, silent: false }).then(function (res) {
                        if (res.ok) {
                            XFAdmin.toast({ body: (res.data && res.data.message) || '批量操作成功', variant: 'success' });
                            // 服务端模式重新请求，本地模式直接重绘
                            if (reload && table.ajax && typeof table.ajax.url === 'function' && table.ajax.url()) {
                                table.ajax.reload(null, false);
                            } else {
                                table.draw(false);
                            }
                            $el.find('tbody .xf-dt-row-check, thead .xf-dt-select-all').prop('checked', false);
                            refreshBar();
                        }
                    });
                };
                if (confirmMsg) {
                    if (window.confirm(confirmMsg)) run();
                } else {
                    run();
                }
            });
        }
    };

    /* ------------------------------------------------------------------
     * 通用表单弹窗 XFAdmin.formDialog(opts)
     *   opts.title       标题
     *   opts.size        modal 尺寸（默认 lg）
     *   opts.fields      字段数组 [{name,label,type,options,placeholder,required,readonly,disabled,rows,help,on,off,value}]
     *                    type: text|email|number|password|date|datetime|time|color|textarea|select|switch|checkbox|radio|hidden|static
     *   opts.values      初始值对象（如 DataTable 行数据）
     *   opts.submitText  提交按钮文案（默认「保存」）
     *   opts.onSubmit    function(data, done)  done(true) 关闭弹窗 / done(false) 保留弹窗
     * ---------------------------------------------------------------- */
    function formControlHtml(f, values) {
        var name = str(f.name);
        var raw = (f.value !== undefined) ? f.value : (values && values[name] !== undefined && values[name] !== null ? values[name] : '');
        var v = str(raw);
        var type = f.type || 'text';
        var idAttr = 'xf-ff-' + name.replace(/[^\w-]/g, '_');
        var base = ' name="' + escapeHtml(name) + '" id="' + escapeHtml(idAttr) + '"' +
            (f.required ? ' required' : '') + (f.readonly ? ' readonly' : '') + (f.disabled ? ' disabled' : '') +
            (f.placeholder ? ' placeholder="' + escapeHtml(f.placeholder) + '"' : '');
        var control = '';
        if (type === 'hidden') return '<input type="hidden"' + base + ' value="' + escapeHtml(v) + '">';
        if (type === 'static') {
            control = '<input type="text" class="form-control" readonly name="' + escapeHtml(name) + '" value="' + escapeHtml(v) + '">';
        } else if (type === 'textarea') {
            control = '<textarea class="form-control"' + base + ' rows="' + (parseInt(f.rows, 10) || 3) + '">' + escapeHtml(v) + '</textarea>';
        } else if (type === 'select') {
            var opts = f.options || {};
            var pairs = Array.isArray(opts) ? opts.map(function (o) { return [o, o]; }) : Object.keys(opts).map(function (k) { return [k, opts[k]]; });
            var selVals = f.multiple ? str(v).split(',') : [v];
            control = '<select class="form-select"' + base + (f.multiple ? ' multiple' : '') + '>' +
                (f.multiple || f.required ? '' : '<option value="">请选择</option>') +
                pairs.map(function (p) {
                    var selected = selVals.indexOf(str(p[0])) !== -1 ? ' selected' : '';
                    return '<option value="' + escapeHtml(p[0]) + '"' + selected + '>' + escapeHtml(p[1]) + '</option>';
                }).join('') + '</select>';
        } else if (type === 'switch' || type === 'checkbox') {
            var on = f.on !== undefined ? f.on : 1;
            var checked = str(raw) === str(on) || raw === true || raw === 1 || raw === '1' ? ' checked' : '';
            control = '<div class="form-check' + (type === 'switch' ? ' form-switch' : '') + ' mt-1">' +
                '<input class="form-check-input" type="checkbox"' + base + checked + '></div>';
        } else if (type === 'radio') {
            var ropts = f.options || {};
            var rpairs = Array.isArray(ropts) ? ropts.map(function (o) { return [o, o]; }) : Object.keys(ropts).map(function (k) { return [k, ropts[k]]; });
            control = '<div class="mt-1">' + rpairs.map(function (p, i) {
                var checked2 = str(p[0]) === v ? ' checked' : '';
                return '<div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="' + escapeHtml(name) + '" id="' + escapeHtml(idAttr + '-' + i) + '" value="' + escapeHtml(p[0]) + '"' + checked2 + '>' +
                    '<label class="form-check-label" for="' + escapeHtml(idAttr + '-' + i) + '">' + escapeHtml(p[1]) + '</label></div>';
            }).join('') + '</div>';
        } else if (type === 'color') {
            control = '<input type="color" class="form-control form-control-color"' + base + ' value="' + escapeHtml(/^#[0-9a-fA-F]{3,8}$/.test(v) ? v : '#563d7c') + '">';
        } else {
            var inputType = { datetime: 'datetime-local', number: 'number', date: 'date', time: 'time', email: 'email', password: 'password' }[type] || 'text';
            control = '<input type="' + inputType + '" class="form-control"' + base + ' value="' + escapeHtml(v) + '"' +
                (f.min !== undefined ? ' min="' + escapeHtml(f.min) + '"' : '') +
                (f.max !== undefined ? ' max="' + escapeHtml(f.max) + '"' : '') +
                (f.step !== undefined ? ' step="' + escapeHtml(f.step) + '"' : '') + '>';
        }
        return '<div class="mb-3' + (f.col ? ' xf-col' : '') + '">' +
            '<label class="form-label" for="' + escapeHtml(idAttr) + '">' + escapeHtml(f.label || name) +
            (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>' + control +
            (f.help ? '<div class="form-text">' + escapeHtml(f.help) + '</div>' : '') + '</div>';
    }

    XFAdmin.formDialog = function (opts) {
        opts = opts || {};
        if (!global.bootstrap || !global.bootstrap.Modal) return null;
        var fields = opts.fields || [];
        var values = opts.values || {};
        var old = document.getElementById('xf-form-modal');
        if (old) old.remove();
        var modalEl = document.createElement('div');
        modalEl.id = 'xf-form-modal';
        modalEl.className = 'modal fade';
        modalEl.tabIndex = -1;
        modalEl.innerHTML = '<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-' + escapeHtml(opts.size || 'lg') + '">' +
            '<div class="modal-content"><div class="modal-header"><h5 class="modal-title">' + escapeHtml(opts.title || '编辑') + '</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
            '<div class="modal-body"><form class="xf-dialog-form" novalidate>' +
            fields.map(function (f) { return formControlHtml(f, values); }).join('') +
            '</form></div>' +
            '<div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>' +
            '<button type="button" class="btn btn-primary xf-form-submit">' + escapeHtml(opts.submitText || '保存') + '</button></div></div></div>';
        document.body.appendChild(modalEl);
        var modal = new global.bootstrap.Modal(modalEl);
        var form = modalEl.querySelector('form');
        var submitBtn = modalEl.querySelector('.xf-form-submit');
        var collect = function () {
            var data = {};
            fields.forEach(function (f) {
                var name = f.name;
                if (!name || f.type === 'static' || f.disabled) return;
                if (f.type === 'switch' || f.type === 'checkbox') {
                    var cb = form.querySelector('[name="' + idSafe(name) + '"]');
                    data[name] = cb && cb.checked ? (f.on !== undefined ? f.on : 1) : (f.off !== undefined ? f.off : 0);
                    return;
                }
                if (f.type === 'radio') {
                    var checked = form.querySelector('[name="' + idSafe(name) + '"]:checked');
                    data[name] = checked ? checked.value : '';
                    return;
                }
                var input = form.querySelector('[name="' + idSafe(name) + '"]');
                if (!input) return;
                data[name] = input.multiple ? Array.from(input.selectedOptions).map(function (o) { return o.value; }) : input.value;
            });
            return data;
        };
        var submit = function () {
            if (!form.checkValidity()) { form.classList.add('was-validated'); form.reportValidity(); return; }
            var data = collect();
            if (typeof opts.onSubmit !== 'function') { modal.hide(); return; }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + escapeHtml(opts.submitText || '保存');
            opts.onSubmit(data, function (close) {
                submitBtn.disabled = false;
                submitBtn.textContent = opts.submitText || '保存';
                if (close !== false) modal.hide();
            });
        };
        submitBtn.addEventListener('click', submit);
        form.addEventListener('submit', function (e) { e.preventDefault(); submit(); });
        modalEl.addEventListener('hidden.bs.modal', function () { modalEl.remove(); });
        modal.show();
        modalEl.addEventListener('shown.bs.modal', function () {
            var first = form.querySelector('input:not([type=hidden]),select,textarea');
            if (first) first.focus();
        }, { once: true });
        return modal;
    };

    /**
     * 将字段名安全地嵌入 CSS 属性选择器（转义引号/反斜杠，保留 [] 等合法字符）。
     * 注意：不能剥离字符（如 roles[] → roles 会导致 querySelector 匹配不到而静默丢字段）。
     */
    function idSafe(name) { return String(name).replace(/\\/g, '\\\\').replace(/"/g, '\\"'); }

    /**
     * 行编辑（DataTable actions 的 action=edit 使用，也可独立调用）。
     * cfg 支持两种模式：
     *  1) page 模式（推荐）：cfg.page 为编辑页地址，以弹窗加载服务端编辑页面内容；
     *  2) fields 模式：cfg.fields 为字段配置，前端自动生成表单。
     * 其余字段：url/method/title/size/reload/frame/viewTitle
     */
    XFAdmin.editRow = function (row, cfg, tableEl) {
        row = row || {};
        cfg = cfg || {};
        // 模式一：加载服务端编辑页面（支持完整表单与富交互），以弹窗展示
        if (cfg.page) {
            // 默认以「片段注入」方式加载（继承页面样式 + 表单自动接管）；
            // 仅当显式 frame:true 时才使用 iframe（适合返回完整独立页面的场景）。
            XFAdmin.editPage(cfg.page, {
                title: cfg.title || '编辑',
                size: cfg.size || 'lg',
                frame: cfg.frame === true,
                tableEl: tableEl,
                reload: cfg.reload !== false,
                row: row
            });
            return;
        }
        // 模式二：前端按字段配置自动生成表单
        var fields = cfg.fields;
        if (!fields || !fields.length) {
            // 自动生成表单时复用表格列头中文名作为字段标签
            var meta = (tableEl && tableEl.__xfColMeta) || {};
            fields = [];
            Object.keys(row).forEach(function (k) {
                var v = row[k];
                if (v !== null && typeof v === 'object') return;
                fields.push({ name: k, label: (meta[k] && meta[k].label) || k, type: k === 'id' ? 'static' : 'text' });
            });
        }
        XFAdmin.formDialog({
            title: cfg.title || '编辑',
            size: cfg.size,
            fields: fields,
            values: row,
            submitText: '保存',
            onSubmit: function (data, done) {
                document.dispatchEvent(new CustomEvent('xf:edit-save', { detail: { row: row, data: data, table: tableEl && tableEl.__xfTable } }));
                if (!cfg.url) { done(true); return; }
                XFAdmin.request(cfg.url, { method: cfg.method || 'PUT', data: data }).then(function (res) {
                    if (res.ok) {
                        XFAdmin.toast({ body: (res.data && res.data.message) || '保存成功', variant: 'success' });
                        done(true);
                        if (cfg.reload !== false && tableEl) XFAdmin.reloadTable(tableEl);
                    } else {
                        done(false);
                    }
                });
            }
        });
    };

    /**
     * 页面弹窗（通用底层）：以弹窗形式拉取并展示一个服务端页面。
     * url  页面地址，需返回含 [data-xf-page-content] 的内容片段或完整页面
     * opts { title, size, frame, tableEl, reload, row, onLoaded }
     * 页面内的 <form> 会被自动接管：提交时以 AJAX 方式提交到 form.action（支持 _method 伪方法），
     * 成功后刷新表格并关闭弹窗；422 校验错误回填到对应字段。
     * editPage / createPage 为语义化别名（默认标题不同）。
     */
    XFAdmin.pageDialog = function (url, opts) {
        opts = opts || {};
        if (!global.bootstrap || !global.bootstrap.Modal) return;
        var modalEl = document.getElementById('xf-edit-modal');
        if (modalEl) modalEl.remove();
        modalEl = document.createElement('div');
        modalEl.id = 'xf-edit-modal';
        modalEl.className = 'modal fade';
        modalEl.tabIndex = -1;
        var sizeCls = opts.size ? (' modal-' + opts.size) : ' modal-lg';
        var maxBtn = opts.maximizable !== false
            ? '<button type="button" class="btn-close-wrap xf-modal-maximize" title="最大化" aria-label="最大化"><i class="ti ti-arrows-maximize"></i></button>'
            : '';
        modalEl.innerHTML = '<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable' + sizeCls + '">' +
            '<div class="modal-content"><div class="modal-header"><h5 class="modal-title">' + escapeHtml(opts.title || '') + '</h5>' +
            '<div class="xf-modal-header-tools">' + maxBtn +
            '<button type="button" class="btn-close-wrap" data-bs-dismiss="modal" aria-label="关闭"><i class="ti ti-x"></i></button></div></div>' +
            '<div class="modal-body"><div class="xf-page-loading"><span class="spinner-border spinner-border-sm text-primary"></span>' +
            '<span class="ms-2 text-muted">正在加载页面…</span></div></div></div></div>';
        document.body.appendChild(modalEl);
        var modal = new global.bootstrap.Modal(modalEl);
        var body = modalEl.querySelector('.modal-body');
        modal.show();
        // 最大化 / 还原切换
        var maxBtnEl = modalEl.querySelector('.xf-modal-maximize');
        if (maxBtnEl) {
            maxBtnEl.addEventListener('click', function () {
                var on = modalEl.classList.toggle('xf-modal-maximized');
                maxBtnEl.querySelector('i').className = on ? 'ti ti-arrows-minimize' : 'ti ti-arrows-maximize';
                maxBtnEl.setAttribute('title', on ? '还原' : '最大化');
            });
        }
        // 弹窗与父页面交互：记录配置，供 iframe 子页 postMessage 桥（XFAdmin.dialogBridge）
        // 与关闭回调使用；关闭时按需刷新表格/页面，并派发 xf:dialog-closed 事件
        modalEl.__xfOpts = opts;
        modalEl.__xfSavedOk = false;
        modalEl.__xfTableId = (opts.tableEl && (opts.tableEl.id || (opts.tableEl.$ && opts.tableEl.$.id))) || '';
        modalEl.addEventListener('hidden.bs.modal', function () {
            // 仅当编辑页内表单成功提交（savedOk）且配置允许 reload 时才刷新表格/页面，
            // 满足「点击关闭时由父页面决定是否刷新」：纯关闭不刷新，保存成功才刷新。
            var wantReload = !!modalEl.__xfSavedOk && opts.reload !== false;
            var reloaded = false;
            if (wantReload) {
                if (opts.tableEl) { XFAdmin.reloadTable(opts.tableEl); reloaded = true; }
                else if (opts.reloadPage) { global.location.reload(); reloaded = true; }
            }
            if (typeof opts.onClose === 'function') { try { opts.onClose(reloaded); } catch (e) { /* 用户回调异常不影响清理 */ } }
            try {
                document.dispatchEvent(new CustomEvent('xf:dialog-closed', {
                    detail: { url: url, reloaded: reloaded, tableId: modalEl.__xfTableId, saved: !!modalEl.__xfSavedOk }
                }));
            } catch (e) { /* 老浏览器忽略 */ }
            modalEl.remove();
        });

        // iframe 模式：直接内嵌完整页面（子页可调用 XFAdmin.dialogBridge.* 与父页交互）
        if (opts.frame) {
            body.innerHTML = '<iframe class="xf-edit-frame" src="' + escapeHtml(url) + '"></iframe>';
            return modal;
        }
        // 内容模式：拉取页面 HTML，提取主内容注入弹窗
        XFAdmin.request(url, { method: 'GET', raw: true, silent: true }).then(function (res) {
            if (!res.ok) {
                body.innerHTML = '<div class="alert alert-danger m-3 mb-0">页面加载失败（' + (res.status || '?') + '）</div>';
                return;
            }
            var content = extractPageContent(res.raw || '');
            body.innerHTML = content || '<div class="alert alert-warning m-3 mb-0">页面未返回有效内容</div>';
            var form = body.querySelector('form');
            if (form) {
                XFAdmin.handleRemoteForm(form, {
                    tableEl: opts.tableEl,
                    reload: opts.reload !== false,
                    modal: modal
                });
                var first = form.querySelector('input:not([type=hidden]),select,textarea');
                if (first) setTimeout(function () { first.focus(); }, 80);
            }
            if (typeof opts.onLoaded === 'function') opts.onLoaded(body, modal);
        });
        return modal;
    };

    /** 编辑页弹窗（pageDialog 别名，默认标题「编辑」） */
    XFAdmin.editPage = function (url, opts) {
        opts = opts || {};
        if (!opts.title) opts.title = '编辑';
        return XFAdmin.pageDialog(url, opts);
    };

    /** 新建页弹窗（pageDialog 别名，默认标题「新增」） */
    XFAdmin.createPage = function (url, opts) {
        opts = opts || {};
        if (!opts.title) opts.title = '新增';
        return XFAdmin.pageDialog(url, opts);
    };

    /**
     * 声明式页面弹窗触发器：任意元素加 data-xf-page-dialog="url" 即可点击弹窗加载该页面。
     * 可选属性：data-xf-title / data-xf-size(sm|lg|xl) / data-xf-frame(iframe 模式) /
     *          data-xf-table("#表格id"，提交成功后自动刷新) / data-xf-reload="false"
     */
    document.addEventListener('click', function (e) {
        if (!e.target || typeof e.target.closest !== 'function') return;
        var trigger = e.target.closest('[data-xf-page-dialog]');
        if (!trigger) return;
        e.preventDefault();
        var url = trigger.getAttribute('data-xf-page-dialog') || trigger.getAttribute('href');
        if (!url) return;
        var tableSel = trigger.getAttribute('data-xf-table');
        XFAdmin.pageDialog(url, {
            title: trigger.getAttribute('data-xf-title') || trigger.textContent.replace(/\s+/g, ' ').trim(),
            size: trigger.getAttribute('data-xf-size') || 'lg',
            frame: trigger.hasAttribute('data-xf-frame'),
            tableEl: tableSel ? document.querySelector(tableSel) : null,
            reload: trigger.getAttribute('data-xf-reload') !== 'false'
        });
    });

    // 从完整页面 HTML 中提取主内容（优先 [data-xf-page-content]，回退 main/.content-page/.card/.container-fluid）
    function extractPageContent(html) {
        try {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var pick = doc.querySelector('[data-xf-page-content]') || doc.querySelector('main') ||
                doc.querySelector('.content-page') || doc.querySelector('.card') || doc.querySelector('.container-fluid');
            return pick ? pick.innerHTML : '';
        } catch (e) { /* noop */ }
        return '';
    }

    /**
     * 自动接管一个远程表单：拦截提交 -> AJAX 提交 -> 成功刷新/关闭，错误回填。
     * cfg { tableEl, reload, modal }
     */
    XFAdmin.handleRemoteForm = function (form, cfg) {
        cfg = cfg || {};
        form.classList.add('xf-remote-form');
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            // 清除上一次校验错误标记
            Array.prototype.forEach.call(form.querySelectorAll('.is-invalid'), function (el) { el.classList.remove('is-invalid'); });
            Array.prototype.forEach.call(form.querySelectorAll('.invalid-feedback.xf-field-error'), function (el) { el.remove(); });
            var action = form.getAttribute('action') || '';
            var spoof = form.querySelector('input[name="_method"]');
            var method = spoof ? spoof.value : (form.getAttribute('method') || 'POST');
            var submitBtn = form.querySelector('button[type="submit"]');
            var savedHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>保存中…';
            }
            XFAdmin.request(action, { method: method, data: new FormData(form) }).then(function (res) {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = savedHtml; }
                if (res.ok) {
                    XFAdmin.toast({ body: (res.data && (res.data.message || res.data.msg)) || '保存成功', variant: 'success' });
                    // 标记本次弹窗为「已保存成功」，关闭时父页面据以决定是否刷新表格
                    var rootModal = cfg.modal && cfg.modal._element;
                    if (rootModal) rootModal.__xfSavedOk = true;
                    if (cfg.modal) {
                        // 弹窗模式：交给定制关闭事件统一处理刷新决策（关闭弹窗即触发 xf:dialog-closed）
                        cfg.modal.hide();
                    } else if (cfg.reload !== false && cfg.tableEl) {
                        XFAdmin.reloadTable(cfg.tableEl);
                    }
                } else if (res.errors) {
                    var firstErr = '';
                    Object.keys(res.errors).forEach(function (k) {
                        var input = form.querySelector('[name="' + idSafe(k) + '"]');
                        if (input) {
                            input.classList.add('is-invalid');
                            var msg = Array.isArray(res.errors[k]) ? res.errors[k][0] : res.errors[k];
                            firstErr = firstErr || msg;
                            var fb = document.createElement('div');
                            fb.className = 'invalid-feedback xf-field-error';
                            fb.textContent = msg;
                            input.parentNode.insertBefore(fb, input.nextSibling);
                        } else if (!firstErr) {
                            firstErr = Array.isArray(res.errors[k]) ? res.errors[k][0] : res.errors[k];
                        }
                    });
                    XFAdmin.toast({ body: firstErr || ((res.data && (res.data.message || res.data.msg)) || '保存失败'), variant: 'danger' });
                }
            });
        });
    };

    /* 通用弹窗 */
    XFAdmin.dialog = function (opts) {
        opts = opts || {};
        if (!global.bootstrap || !global.bootstrap.Modal) return;
        var modalEl = document.getElementById('xf-dialog-modal');
        if (modalEl) modalEl.remove();
        modalEl = document.createElement('div');
        modalEl.id = 'xf-dialog-modal';
        modalEl.className = 'modal fade';
        modalEl.tabIndex = -1;
        modalEl.innerHTML = '<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable' + (opts.size ? ' modal-' + opts.size : '') + '">' +
            '<div class="modal-content"><div class="modal-header"><h5 class="modal-title">' + escapeHtml(opts.title || '') + '</h5>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>' +
            '<div class="modal-body">' + (opts.body || '') + '</div>' +
            (opts.footer ? '<div class="modal-footer">' + opts.footer + '</div>' : '') + '</div></div>';
        document.body.appendChild(modalEl);
        var modal = new global.bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', function () { modalEl.remove(); });
        modal.show();
        modalEl.addEventListener('shown.bs.modal', function () {
            var focusable = modalEl.querySelector('input:not([type=hidden]),select,textarea,button.btn-primary,[data-xf-autofocus]');
            if (focusable) focusable.focus();
        }, { once: true });
        return modal;
    };

    /* 开关 / 输入框 变更提交 */
    document.addEventListener('change', function (e) {
        var target = e.target;
        // 状态开关
        if (target.classList && target.classList.contains('xf-cell-switch')) {
            var value = target.checked ? target.getAttribute('data-xf-on') : target.getAttribute('data-xf-off');
            var detail = { el: target, checked: target.checked, value: value, field: target.getAttribute('data-xf-field'), id: target.getAttribute('data-xf-id'), row: rowOf(target) };
            document.dispatchEvent(new CustomEvent('xf:switch', { detail: detail }));
            var url = target.getAttribute('data-xf-url');
            if (!url) return;
            target.disabled = true;
            var payload = {};
            payload[target.getAttribute('data-xf-field') || 'status'] = value;
            payload.id = target.getAttribute('data-xf-id');
            XFAdmin.request(url, { method: 'POST', data: payload }).then(function (res) {
                target.disabled = false;
                if (res.ok) {
                    XFAdmin.toast({ body: (res.data && res.data.message) || '状态已更新', variant: 'success', delay: 1500 });
                } else {
                    target.checked = !target.checked; // 失败回滚
                }
            });
            return;
        }
        // 单元格输入框
        if (target.classList && target.classList.contains('xf-cell-input')) {
            var detail2 = { el: target, value: target.value, field: target.getAttribute('data-xf-field'), id: target.getAttribute('data-xf-id'), row: rowOf(target) };
            document.dispatchEvent(new CustomEvent('xf:cell-input', { detail: detail2 }));
            var url2 = target.getAttribute('data-xf-url');
            if (!url2) return;
            var payload2 = {};
            payload2[target.getAttribute('data-xf-field') || 'value'] = target.value;
            payload2.id = target.getAttribute('data-xf-id');
            XFAdmin.request(url2, { method: 'POST', data: payload2 }).then(function (res) {
                if (res.ok) XFAdmin.toast({ body: (res.data && res.data.message) || '已保存', variant: 'success', delay: 1500 });
            });
            return;
        }
        // 可编辑下拉单元格（select 渲染器）
        if (target.classList && target.classList.contains('xf-cell-select')) {
            var detail3 = { el: target, value: target.value, field: target.getAttribute('data-field'), row: rowOf(target) };
            document.dispatchEvent(new CustomEvent('xf:cell-select', { detail: detail3 }));
            var url3 = target.getAttribute('data-url');
            if (!url3) return;
            var payload3 = {};
            payload3[target.getAttribute('data-field') || 'value'] = target.value;
            target.disabled = true;
            XFAdmin.request(url3, { method: 'POST', data: payload3 }).then(function (res) {
                target.disabled = false;
                if (res.ok) XFAdmin.toast({ body: (res.data && res.data.message) || '已更新', variant: 'success', delay: 1500 });
            });
        }
    });

    // ---------- 静态交互表格（对标 INSPINIA custom-table.js 的 data-table-* 体系） ----------
    // 用于 Orders / Customers / Clients 等"静态数据 + 前端交互"表格：
    //   搜索 [data-xftable-search]、下拉筛选 [data-xftable-filter=行data键]、
    //   每页条数 [data-xftable-pagesize]、全选 [data-xftable-check-all]、
    //   批量删除 [data-xftable-delete]、单行删除 [data-xftable-delete-row]、
    //   分页容器 [data-xftable-pagination] 与统计 [data-xftable-info]。
    // 服务端大数据量场景请使用 DataTable 组件（ajax + server_side + filter_bar）。
    XFAdmin.register('xftable', function (root, config) {
        config = config || {};
        var table = root.querySelector('table');
        if (!table || !table.tBodies.length) return;
        var tbody = table.tBodies[0];
        var pageSize = parseInt(config.pageSize, 10) || 10;
        var page = 1;
        var q = '';
        var filters = {}; // { data键: 值 }

        function allRows() { return Array.prototype.slice.call(tbody.rows); }

        // 行是否命中当前 搜索词 + 全部筛选条件
        function match(tr) {
            if (q && (tr.textContent || '').toLowerCase().indexOf(q) === -1) return false;
            for (var k in filters) {
                if (filters[k] && (tr.getAttribute('data-' + k) || '') !== filters[k]) return false;
            }
            return true;
        }

        function render() {
            var rows = allRows();
            var hits = rows.filter(match);
            var pages = Math.max(1, Math.ceil(hits.length / pageSize));
            if (page > pages) page = pages;
            var start = (page - 1) * pageSize;
            rows.forEach(function (tr) { tr.style.display = 'none'; });
            hits.slice(start, start + pageSize).forEach(function (tr) { tr.style.display = ''; });
            // 统计信息（如"显示 1-10 / 共 23 条"）
            var info = root.querySelector('[data-xftable-info]');
            if (info) {
                info.textContent = hits.length
                    ? '显示 ' + (start + 1) + '-' + Math.min(start + pageSize, hits.length) + ' 条，共 ' + hits.length + ' 条'
                    : '无匹配数据';
            }
            // 分页按钮
            var pg = root.querySelector('[data-xftable-pagination]');
            if (pg) {
                var h = '<ul class="pagination pagination-boxed mb-0 justify-content-end">';
                h += '<li class="page-item' + (page <= 1 ? ' disabled' : '') + '"><a class="page-link" href="#" data-pg="prev"><i class="ti ti-chevron-left"></i></a></li>';
                for (var i = 1; i <= pages; i++) {
                    if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - page) > 1) {
                        if (i === 3 || i === pages - 2) h += '<li class="page-item disabled"><span class="page-link">…</span></li>';
                        continue;
                    }
                    h += '<li class="page-item' + (i === page ? ' active' : '') + '"><a class="page-link" href="#" data-pg="' + i + '">' + i + '</a></li>';
                }
                h += '<li class="page-item' + (page >= pages ? ' disabled' : '') + '"><a class="page-link" href="#" data-pg="next"><i class="ti ti-chevron-right"></i></a></li></ul>';
                pg.innerHTML = h;
            }
            syncCheckAll();
        }

        // 全选框与行勾选状态同步（只作用于当前可见行）
        function visChecks() {
            return allRows().filter(function (tr) { return tr.style.display !== 'none'; })
                .map(function (tr) { return tr.querySelector('input[type="checkbox"]'); })
                .filter(Boolean);
        }
        function syncCheckAll() {
            var all = root.querySelector('[data-xftable-check-all]');
            if (!all) return;
            var cs = visChecks();
            all.checked = cs.length > 0 && cs.every(function (c) { return c.checked; });
        }

        // 事件委托：分页 / 全选 / 行选 / 删除
        root.addEventListener('click', function (e) {
            var a = e.target.closest('[data-pg]');
            if (a) {
                e.preventDefault();
                var v = a.getAttribute('data-pg');
                if (v === 'prev') page = Math.max(1, page - 1);
                else if (v === 'next') page = page + 1;
                else page = parseInt(v, 10) || 1;
                render();
                return;
            }
            var del = e.target.closest('[data-xftable-delete-row]');
            if (del) {
                e.preventDefault();
                var tr = del.closest('tr');
                if (tr) { tr.remove(); render(); }
                return;
            }
            var bulk = e.target.closest('[data-xftable-delete]');
            if (bulk) {
                e.preventDefault();
                allRows().forEach(function (tr) {
                    var c = tr.querySelector('input[type="checkbox"]');
                    if (c && c.checked) tr.remove();
                });
                render();
            }
        });
        root.addEventListener('change', function (e) {
            if (e.target.matches('[data-xftable-check-all]')) {
                visChecks().forEach(function (c) { c.checked = e.target.checked; });
                return;
            }
            if (e.target.matches('[data-xftable-filter]')) {
                filters[e.target.getAttribute('data-xftable-filter')] = e.target.value;
                page = 1;
                render();
                return;
            }
            if (e.target.matches('[data-xftable-pagesize]')) {
                pageSize = parseInt(e.target.value, 10) || pageSize;
                page = 1;
                render();
                return;
            }
            if (e.target.type === 'checkbox') syncCheckAll();
        });
        var si = root.querySelector('[data-xftable-search]');
        if (si) {
            var t = null;
            si.addEventListener('input', function () {
                clearTimeout(t);
                t = setTimeout(function () { q = si.value.trim().toLowerCase(); page = 1; render(); }, 200);
            });
        }
        render();
    });

    // ---------- 图表 ----------
    // 暗色主题跟随：图表实例登记到全局数组，监听 <html data-bs-theme> 变化后统一重绘。
    // 无论主题由 INSPINIA app.js 还是本包切换，都能捕获（不依赖任何自定义事件）。
    window.__xfApex = window.__xfApex || [];
    window.__xfEchartsMeta = window.__xfEchartsMeta || [];
    window.__xfApexSpecial = window.__xfApexSpecial || [];
    function xfCurrentThemeMode() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }
    window.__xfApplyChartTheme = function (mode) {
        mode = mode || xfCurrentThemeMode();
        // ApexCharts：updateOptions 热切换主题无需重建
        (window.__xfApex || []).forEach(function (c) {
            try { c.updateOptions({ theme: { mode: mode } }, false, false); } catch (e) {}
        });
        // ECharts：theme 在 init 时固定，需 dispose + 重建
        (window.__xfEchartsMeta || []).forEach(function (m) {
            try {
                if (global.echarts && m.el) {
                    global.echarts.dispose(m.el);
                    var t = mode === 'dark' ? 'dark' : (m.forceTheme || null);
                    var inst = global.echarts.init(m.el, t);
                    inst.setOption(m.option || {});
                }
            } catch (e) {}
        });
        // ApexTree / ApexSankey 等专用插件：init 时读主题，热切换需重建
        (window.__xfApexSpecial || []).forEach(function (m) {
            try { if (m.rebuild) m.rebuild(mode); } catch (e) {}
        });
    };
    // 全局监听主题属性变化（一次性绑定）
    if (!window.__xfThemeObserverBound) {
        window.__xfThemeObserverBound = 1;
        var xfThemeObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mu) {
                if (mu.attributeName === 'data-bs-theme') {
                    window.__xfApplyChartTheme();
                }
            });
        });
        xfThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    }

    XFAdmin.register('apexchart', function (el, options) {
        if (!global.ApexCharts) return;
        options = options || {};
        // 未显式指定主题时，跟随当前 data-bs-theme
        if (!options.theme) {
            options = JSON.parse(JSON.stringify(options));
            options.theme = { mode: xfCurrentThemeMode() };
        }
        var chart = new global.ApexCharts(el, options);
        chart.render();
        window.__xfApex.push(chart);
        // 响应式：监听窗口 resize，调用 chart.resize() 适应容器宽度变化
        // 使用防抖避免频繁调用，与 ECharts 一致
        if (!window.__xfApexResizeBound) {
            window.__xfApexResizeBound = 1;
            var apexResizeTimer = null;
            window.addEventListener('resize', function () {
                if (apexResizeTimer) clearTimeout(apexResizeTimer);
                apexResizeTimer = setTimeout(function () {
                    (window.__xfApex || []).forEach(function (c) {
                        try { c.resize(); } catch (e) {}
                    });
                }, 150);
            });
        }
        return chart;
    });

    XFAdmin.register('echart', function (el, config) {
        if (!global.echarts) return;
        config = config || {};
        // 主题：PHP 显式传 theme 则强制使用该名；否则跟随当前 data-bs-theme（dark 用内置 'dark'）
        var forceTheme = config.theme || null;
        var initTheme = forceTheme || (xfCurrentThemeMode() === 'dark' ? 'dark' : null);
        var option = config.options || {};
        var chart = global.echarts.init(el, initTheme);
        chart.setOption(option);
        // 登记实例用于主题热切换重建
        window.__xfEchartsMeta.push({ el: el, option: option, forceTheme: forceTheme });
        window.__xfEcharts = window.__xfEcharts || [];
        window.__xfEcharts.push(chart);
        if (!window.__xfEchartResizeBound) {
            window.__xfEchartResizeBound = 1;
            window.addEventListener('resize', function () {
                (window.__xfEcharts || []).forEach(function (c) { try { c.resize(); } catch (e) {} });
            });
        }
        return chart;
    });

    XFAdmin.register('vectormap', function (el, config) {
        if (!global.jsVectorMap) return;
        config.selector = '#' + el.id;
        return new global.jsVectorMap(config);
    });

    // ---------- 表单增强 ----------
    XFAdmin.register('choices', function (el, config) {
        if (!global.Choices) return;
        return new global.Choices(el, Object.assign({
            removeItemButton: el.multiple,
            allowHTML: false,
            searchEnabled: true,
            itemSelectText: ''
        }, config));
    });

    XFAdmin.register('select2', function (el, config) {
        if (!global.jQuery || !global.jQuery.fn.select2) return;
        return global.jQuery(el).select2(Object.assign({ width: '100%' }, config));
    });

    XFAdmin.register('daterangepicker', function (el, config) {
        if (!global.jQuery || !global.jQuery.fn.daterangepicker) return;
        // 中文本地化（moment 存在时同步切换 zh-cn，周一为一周首日）
        if (global.moment && typeof global.moment.locale === 'function') {
            try { global.moment.locale('zh-cn'); } catch (e) { /* 语言包缺失时忽略 */ }
        }
        var zhLocale = {
            applyLabel: '确定', cancelLabel: '取消', fromLabel: '从', toLabel: '到',
            customRangeLabel: '自定义范围', weekLabel: '周', firstDay: 1,
            daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
            monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月']
        };
        var opts = Object.assign({}, config);
        opts.locale = Object.assign({}, zhLocale, config.locale || {});
        if (opts.xfRanges && global.moment) {
            var m = global.moment;
            opts.ranges = {
                '今天': [m(), m()],
                '昨天': [m().subtract(1, 'days'), m().subtract(1, 'days')],
                '最近7天': [m().subtract(6, 'days'), m()],
                '最近30天': [m().subtract(29, 'days'), m()],
                '本月': [m().startOf('month'), m().endOf('month')],
                '上月': [m().subtract(1, 'month').startOf('month'), m().subtract(1, 'month').endOf('month')]
            };
        }
        delete opts.xfRanges;
        return global.jQuery(el).daterangepicker(opts);
    });

    XFAdmin.register('slider', function (el, config) {
        if (!global.noUiSlider) return;
        var inputName = config.input;
        delete config.input;
        global.noUiSlider.create(el, config);
        if (inputName) {
            var input = el.parentElement.querySelector('input[name="' + inputName + '"]') ||
                document.querySelector('input[name="' + inputName + '"]');
            if (input) {
                el.noUiSlider.on('update', function (values) {
                    input.value = values.map(function (v) { return parseFloat(v); }).join(',');
                });
            }
        }
        return el.noUiSlider;
    });

    XFAdmin.register('quill', function (el, config) {
        if (!global.Quill) return;
        var quill = new global.Quill(el, {
            theme: config.theme || 'snow',
            modules: config.modules || {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['link', 'image', 'code-block'],
                    ['clean']
                ]
            },
            placeholder: config.placeholder || ''
        });
        if (config.input) {
            var input = el.parentElement.querySelector('input[name="' + config.input + '"]');
            if (input) {
                quill.on('text-change', function () {
                    input.value = el.querySelector('.ql-editor').innerHTML;
                });
            }
        }
        return quill;
    });

    XFAdmin.register('summernote', function (el, config) {
        if (!global.jQuery || !global.jQuery.fn.summernote) return;
        return global.jQuery(el).summernote(Object.assign({ height: config.height || 260 }, config.options || {}));
    });

    XFAdmin.register('dropzone', function (el, config) {
        if (!global.Dropzone) return;
        global.Dropzone.autoDiscover = false;
        return new global.Dropzone(el, Object.assign({ url: config.url || '/' }, config));
    });

    XFAdmin.register('filepond', function (el, config) {
        if (!global.FilePond) return;
        ['FilePondPluginImagePreview', 'FilePondPluginFileEncode', 'FilePondPluginFileValidateSize', 'FilePondPluginImageExifOrientation'].forEach(function (p) {
            if (global[p]) { try { global.FilePond.registerPlugin(global[p]); } catch (e) { /* 已注册 */ } }
        });
        return global.FilePond.create(el, config);
    });

    XFAdmin.register('pickr', function (el, config) {
        if (!global.Pickr) return;
        var inputName = config.input;
        delete config.input;
        var pickr = global.Pickr.create(Object.assign({
            el: el,
            theme: config.theme || 'classic',
            default: config.default || '#3e60d5',
            components: {
                preview: true, opacity: true, hue: true,
                interaction: { hex: true, rgba: true, input: true, save: true }
            }
        }, config));
        if (inputName) {
            pickr.on('save', function (color) {
                var input = document.querySelector('input[name="' + inputName + '"]');
                if (input && color) input.value = color.toHEXA().toString();
                pickr.hide();
            });
        }
        return pickr;
    });

    XFAdmin.register('inputmask', function (el, config) {
        if (!global.Inputmask) return;
        return global.Inputmask({ mask: config.mask }).mask(el);
    });

    XFAdmin.register('tagify', function (el, config) {
        if (!global.Tagify) return;
        return new global.Tagify(el, config);
    });

    // ---------- 交互 ----------
    XFAdmin.register('sortable', function (el, config) {
        if (!global.Sortable) return;
        var inputId = el.id + '-input';
        var opts = Object.assign({ animation: 150 }, config);
        delete opts.input;
        if (config.input) {
            opts.onSort = function () {
                var ids = Array.prototype.map.call(el.children, function (c) { return c.getAttribute('data-id'); });
                var input = document.getElementById(inputId);
                if (input) input.value = ids.join(',');
            };
        }
        return global.Sortable.create(el, opts);
    });

    // ---------- 嵌套拖拽列表（对齐 INSPINIA misc-nestable.html 的 .nested-sortable） ----------
    XFAdmin.register('nestable', function (el) {
        if (!global.Sortable) return;
        var handle = el.classList.contains('nested-sortable-handle');
        var lists = el.matches('.nested-sortable, .nested-sortable-handle') ? [el] : [];
        el.querySelectorAll('.nested-sortable, .nested-sortable-handle').forEach(function (l) { lists.push(l); });
        // 隐藏 input 渲染在 data-xf 容器之后（避免被 Sortable 视作可拖拽项），需向外查找
        var input = el.querySelector('input[type="hidden"][data-nestable-input]')
            || (el.nextElementSibling && el.nextElementSibling.matches && el.nextElementSibling.matches('input[data-nestable-input]') ? el.nextElementSibling : null)
            || (el.parentElement ? el.parentElement.querySelector('input[type="hidden"][data-nestable-input]') : null);
        function sync() {
            if (!input) return;
            var ids = Array.prototype.map.call(el.querySelectorAll('[data-id]'), function (n) { return n.getAttribute('data-id'); });
            input.value = ids.join(',');
        }
        lists.forEach(function (list) {
            global.Sortable.create(list, {
                group: 'nested',
                handle: handle ? '.sort-handle' : null,
                ghostClass: 'sortable-item-ghost',
                animation: 150, fallbackOnBody: true, swapThreshold: .65,
                onStart: function (e) { e.item.classList.add('sortable-drag'); },
                onEnd: function (e) { e.item.classList.remove('sortable-drag'); sync(); }
            });
        });
        sync();
    });

    XFAdmin.register('jstree', function (el, config) {
        if (!global.jQuery || !global.jQuery.fn.jstree) return;
        return global.jQuery(el).jstree(config);
    });

    XFAdmin.register('lightbox', function (el, config) {
        if (!global.GLightbox) return;
        var masonry = config.masonry;
        delete config.masonry;
        var lb = global.GLightbox(Object.assign({ selector: null }, config, { selector: config.selector }));
        if (masonry && global.Masonry) {
            new global.Masonry(el, { percentPosition: true });
        }
        return lb;
    });

    // ---------- 瀑布流（对齐 INSPINIA misc-masonry.html：row + col-* + Masonry.js） ----------
    XFAdmin.register('masonry', function (el) {
        if (!global.Masonry) return;
        var cells = el.querySelectorAll('.masonry-cell');
        if (!cells.length) return;
        var msnry = new global.Masonry(el, {
            itemSelector: '.masonry-cell',
            percentPosition: true,
            columnWidth: cells[0],
            gutter: 0
        });
        // 图片加载完成后重排，避免高度抖动
        if (global.imagesLoaded) {
            global.imagesLoaded(el).on('progress', function () { msnry.layout(); });
        } else {
            setTimeout(function () { msnry.layout(); }, 300);
        }
        return msnry;
    });

    XFAdmin.register('calendar', function (el, config) {
        if (!global.FullCalendar) return;
        config = config || {};
        // 中文本地化（无需额外 locale 文件，直接覆盖按钮/文案）
        var zhDefaults = {
            locale: 'zh-cn',
            firstDay: 1,
            buttonText: { today: '今天', month: '月', week: '周', day: '日', list: '列表' },
            allDayText: '全天',
            noEventsText: '暂无日程',
            moreLinkText: function (n) { return '还有 ' + n + ' 项'; }
        };
        var opts = Object.assign({}, zhDefaults, config);
        delete opts.externalEvents;
        delete opts.interactive;
        // 默认交互闭环：点击日期新建日程、点击日程查看/删除、拖拽调整提示（interactive=false 可关闭）
        if (config.interactive !== false) {
            opts.selectable = true;
            opts.editable = opts.editable !== false;
            if (!opts.dateClick) {
                opts.dateClick = function (info) {
                    XFAdmin.formDialog({
                        title: '新建日程（' + info.dateStr + '）',
                        fields: [
                            { name: 'title', label: '日程标题', required: true, placeholder: '请输入日程内容' },
                            { name: 'variant', label: '颜色', type: 'select', options: { primary: '蓝色', success: '绿色', warning: '橙色', danger: '红色', info: '青色' } }
                        ],
                        onSubmit: function (data, done) {
                            if (data.title) {
                                calendar.addEvent({ title: data.title, start: info.dateStr, allDay: true, className: 'bg-' + (data.variant || 'primary') });
                                XFAdmin.toast({ body: '日程「' + data.title + '」已添加', variant: 'success' });
                            }
                            done(true);
                        }
                    });
                };
            }
            if (!opts.eventClick) {
                opts.eventClick = function (info) {
                    var ev = info.event;
                    var when = ev.start ? ev.start.toLocaleString('zh-CN') : '';
                    XFAdmin.confirm({
                        title: ev.title,
                        text: '开始时间：' + when + (ev.allDay ? '（全天）' : '') + '。是否删除该日程？',
                        icon: 'info',
                        confirmText: '删除该日程', cancelText: '关闭',
                        onOk: function () {
                            ev.remove();
                            XFAdmin.toast({ body: '日程已删除', variant: 'success' });
                        }
                    });
                };
            }
            if (!opts.eventDrop) {
                opts.eventDrop = function (info) {
                    XFAdmin.toast({ body: '「' + info.event.title + '」已调整到 ' + info.event.start.toLocaleDateString('zh-CN'), variant: 'info' });
                };
            }
        }
        var calendar = new global.FullCalendar.Calendar(el, opts);
        calendar.render();
        // 外部事件拖拽（对齐 INSPINIA calendar.html 的 #external-events）
        if (config.externalEvents && global.FullCalendar.Draggable) {
            var root = el.closest('[data-calendar-root]') || document;
            var src = root.querySelector('#external-events');
            if (src) {
                new global.FullCalendar.Draggable(src, {
                    itemSelector: '.external-event',
                    eventData: function (node) {
                        return { title: node.innerText, className: node.getAttribute('data-class') };
                    }
                });
            }
        }
        return calendar;
    });

    XFAdmin.register('tour', function (el, config) {
        config = config || {};
        // 兼容 tourguidejs 不同版本的全局暴露：window.tourguide.TourGuideClient / window.TourGuideClient
        var TG = (global.tourguide && global.tourguide.TourGuideClient) || global.TourGuideClient;
        if (!TG) {
            console.warn('[XFAdmin] Tour 插件未加载（tourguidejs）');
            return;
        }
        var tg = new TG(Object.assign({
            steps: config.steps || [],
            nextLabel: '下一步', prevLabel: '上一步', finishLabel: '完成',
            exitOnClickOutside: true, showStepDots: true, showStepProgress: true
        }, config.options || {}));
        var start = function () {
            try { tg.start(); } catch (e) { console.warn('[XFAdmin] Tour 启动失败', e); }
        };
        // 触发方式：auto 自动播放；trigger 选择器指定启动按钮；宿主元素自身可点击启动
        if (config.trigger) {
            document.querySelectorAll(config.trigger).forEach(function (btn) { btn.addEventListener('click', start); });
        } else if (!config.auto) {
            el.addEventListener('click', start);
        }
        if (config.auto) setTimeout(start, config.delay || 400);
        return tg;
    });

    XFAdmin.register('clipboard', function (el, config) {
        if (!global.ClipboardJS) return;
        var cb = new global.ClipboardJS(el);
        cb.on('success', function () {
            XFAdmin.toast({ body: (config && config.success) || '已复制！', variant: 'success' });
        });
        return cb;
    });

    XFAdmin.register('sweetalert', function (el, config) {
        if (!global.Swal) return;
        var run = function () {
            global.Swal.fire(config.swal || {}).then(function (result) {
                if (result.isConfirmed) {
                    if (config.confirmUrl) window.location.href = config.confirmUrl;
                    if (config.confirmJs) new Function(config.confirmJs)();
                }
                el.dispatchEvent(new CustomEvent('xf.swal.closed', { detail: result }));
            });
        };
        if (config.auto) { run(); return; }
        el.addEventListener('click', run);
    });

    XFAdmin.register('ladda', function (el) {
        if (!global.Ladda) return;
        var l = global.Ladda.create(el);
        el.addEventListener('click', function () {
            l.start();
            // 表单提交/自定义流程结束后可调用 XFAdmin.get(el).stop()
            if (!el.closest('form')) setTimeout(function () { l.stop(); }, 2000);
        });
        return l;
    });

    // ---------- 数字滚动 ----------
    XFAdmin.register('counter', function (el, config) {
        var target = parseFloat(config.target || el.textContent) || 0;
        var prefix = config.prefix || '';
        var suffix = config.suffix || '';
        var duration = config.duration || 1200;
        var start = null;
        var decimals = (String(config.target).split('.')[1] || '').length;
        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = prefix + (target * eased).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        var observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                requestAnimationFrame(step);
                observer.disconnect();
            }
        });
        observer.observe(el);
    });

    // ---------- AJAX 表单 ----------
    XFAdmin.register('form', function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (form.classList.contains('needs-validation') && !form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            var formHeaders = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
            var csrfToken = XFAdmin.csrf();
            if (csrfToken) formHeaders['X-CSRF-TOKEN'] = csrfToken;
            fetch(form.action || window.location.href, {
                method: form.getAttribute('method') || 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: formHeaders
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                });
            }).then(function (result) {
                var type = result.ok ? 'xf.form.success' : 'xf.form.error';
                form.dispatchEvent(new CustomEvent(type, { detail: result, bubbles: true }));
                if (result.ok) {
                    XFAdmin.toast({ body: (result.data && result.data.message) || '操作成功', variant: 'success' });
                } else {
                    XFAdmin.toast({ body: (result.data && result.data.message) || '请求失败(' + result.status + ')', variant: 'danger' });
                }
            }).catch(function (err) {
                form.dispatchEvent(new CustomEvent('xf.form.error', { detail: err, bubbles: true }));
                XFAdmin.toast({ body: '网络错误，请稍后重试', variant: 'danger' });
            }).finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    });

    // ---------- 分步向导（纯原生） ----------
    XFAdmin.register('wizard', function (el) {
        var panes = el.querySelectorAll('.xf-wizard-pane');
        var navs = el.querySelectorAll('.xf-wizard-nav [data-step]');
        var progress = el.querySelector('.xf-wizard-progress');
        var prevBtn = el.querySelector('.xf-wizard-prev');
        var nextBtn = el.querySelector('.xf-wizard-next');
        var nextLabel = nextBtn ? nextBtn.textContent : '';
        var finishLabel = nextBtn ? (nextBtn.getAttribute('data-finish-label') || '提交') : '';
        var current = 0;
        var total = panes.length;

        function show(i) {
            if (i < 0 || i >= total) return;
            // 校验当前步骤内的表单元素
            if (i > current) {
                var invalid = false;
                panes[current].querySelectorAll('input,select,textarea').forEach(function (f) {
                    if (f.willValidate && !f.checkValidity()) { f.reportValidity(); invalid = true; }
                });
                if (invalid) return;
            }
            current = i;
            panes.forEach(function (p, idx) { p.classList.toggle('d-none', idx !== i); });
            navs.forEach(function (n, idx) {
                n.classList.toggle('active', idx === i);
                n.classList.toggle('done', idx < i);
            });
            if (prevBtn) prevBtn.disabled = i === 0;
            if (nextBtn) nextBtn.textContent = i === total - 1 ? finishLabel : nextLabel;
            if (progress) progress.style.width = Math.round(((i + 1) / total) * 100) + '%';
            el.dispatchEvent(new CustomEvent('xf.wizard.change', { detail: { step: i } }));
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { show(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () {
            if (current === total - 1) {
                el.dispatchEvent(new CustomEvent('xf.wizard.finish', { detail: { step: current }, bubbles: true }));
                var form = el.closest('form');
                if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
            } else { show(current + 1); }
        });
        navs.forEach(function (n, idx) { n.addEventListener('click', function () { if (idx <= current) show(idx); }); });
        show(0);
        return { goTo: show, next: function () { show(current + 1); }, prev: function () { show(current - 1); } };
    });

    // ---------- 倒计时 ----------
    XFAdmin.register('countdown', function (el, config) {
        var deadline = parseInt(el.getAttribute('data-deadline'), 10) || (config && config.deadline);
        if (!deadline) return;
        var units = el.querySelectorAll('[data-unit]');
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function tick() {
            var diff = Math.max(0, deadline - Date.now());
            var s = Math.floor(diff / 1000);
            var map = { days: Math.floor(s / 86400), hours: Math.floor((s % 86400) / 3600), minutes: Math.floor((s % 3600) / 60), seconds: s % 60 };
            units.forEach(function (u) { u.textContent = pad(map[u.getAttribute('data-unit')] || 0); });
            if (diff <= 0) { clearInterval(timer); el.dispatchEvent(new CustomEvent('xf.countdown.end')); }
        }
        tick();
        var timer = setInterval(tick, 1000);
        return { stop: function () { clearInterval(timer); } };
    });

    // ---------- 看板拖拽 + 搜索过滤 ----------
    XFAdmin.register('kanban', function (el, config) {
        if (!global.Sortable) return;
        var lists = el.querySelectorAll('[data-kanban-list]');
        var instances = [];
        lists.forEach(function (list) {
            instances.push(global.Sortable.create(list, {
                group: 'xf-kanban', animation: 150, ghostClass: 'bg-primary-subtle',
                onEnd: function (evt) {
                    el.dispatchEvent(new CustomEvent('xf.kanban.move', {
                        detail: {
                            from: evt.from.parentElement.getAttribute('data-column'),
                            to: evt.to.parentElement.getAttribute('data-column'),
                            oldIndex: evt.oldIndex, newIndex: evt.newIndex, item: evt.item
                        }, bubbles: true
                    }));
                }
            }));
        });
        // 顶栏搜索：按卡片文本过滤（标题/标签/说明/成员），无匹配列头计数实时更新
        var search = el.querySelector('[data-kanban-search]');
        if (search) {
            var t = null;
            search.addEventListener('input', function () {
                clearTimeout(t);
                t = setTimeout(function () {
                    var q = search.value.trim().toLowerCase();
                    el.querySelectorAll('.kanban-board').forEach(function (board) {
                        var hits = 0;
                        board.querySelectorAll('ul[data-kanban-list] > li.kanban-item').forEach(function (li) {
                            var ok = !q || (li.textContent || '').toLowerCase().indexOf(q) !== -1;
                            li.style.display = ok ? '' : 'none';
                            if (ok) hits++;
                        });
                        var cnt = board.querySelector('.kanban-item.py-2.px-3 h5 .text-muted');
                        if (cnt) cnt.textContent = '(' + hits + ')';
                    });
                }, 200);
            });
        }
        // 新增按钮（顶栏/列头）：触发事件，交由宿主处理
        el.querySelectorAll('[data-kanban-add]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                el.dispatchEvent(new CustomEvent('xf.kanban.add', {
                    detail: { column: btn.closest('.kanban-board').getAttribute('data-column') },
                    bubbles: true
                }));
            });
        });
        return instances;
    });

    // ---------- 邮件中心：列表搜索过滤 ----------
    XFAdmin.register('email', function (el) {
        var search = el.querySelector('[data-email-search]');
        if (!search) return;
        var t = null;
        search.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(function () {
                var q = search.value.trim().toLowerCase();
                el.querySelectorAll('.email-table tbody tr').forEach(function (tr) {
                    tr.style.display = (!q || (tr.textContent || '').toLowerCase().indexOf(q) !== -1) ? '' : 'none';
                });
            }, 200);
        });
    });

    // ---------- 聊天窗口 ----------
    XFAdmin.register('chat-scroll', function (el) {
        el.scrollTop = el.scrollHeight;
        return { toBottom: function () { el.scrollTop = el.scrollHeight; } };
    });
    XFAdmin.register('chat-form', function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('[name="message"]');
            var text = (input.value || '').trim();
            if (!text) return;
            form.dispatchEvent(new CustomEvent('xf.chat.send', { detail: { text: text }, bubbles: true }));
            var box = (form.closest('.chat-box') || document).querySelector('[data-xf="chat-scroll"]');
            if (box) {
                var wrap = document.createElement('div');
                wrap.className = 'd-flex mb-3 justify-content-end';
                wrap.innerHTML = '<div class="text-end" style="max-width:75%;"><div class="p-2 px-3 rounded bg-primary text-white"></div></div>';
                wrap.querySelector('div div').textContent = text;
                box.appendChild(wrap);
                box.scrollTop = box.scrollHeight;
            }
            input.value = '';
        });
    });

    // ---------- 问题跟踪（前端搜索过滤） ----------
    XFAdmin.register('issues', function (el) {
        var input = el.querySelector('.xf-issue-search');
        if (!input) return;
        input.addEventListener('input', function () {
            var kw = input.value.trim().toLowerCase();
            el.querySelectorAll('.xf-issue-row').forEach(function (tr) {
                tr.style.display = (!kw || tr.textContent.toLowerCase().indexOf(kw) !== -1) ? '' : 'none';
            });
        });
    });

    // ---------- 投票列表（赞成/反对，单向切换） ----------
    XFAdmin.register('votelist', function (el) {
        el.addEventListener('click', function (e) {
            var up = e.target.closest('.xf-vote-up');
            var down = e.target.closest('.xf-vote-down');
            if (!up && !down) return;
            var box = (up || down).closest('.xf-vote-box');
            if (!box) return;
            var countEl = box.querySelector('.xf-vote-count');
            var count = parseInt(countEl.textContent, 10) || 0;
            var state = box.dataset.voted || ''; // '' | 'up' | 'down'
            if (up) {
                if (state === 'up') { count -= 1; state = ''; }
                else { count += state === 'down' ? 2 : 1; state = 'up'; }
            } else {
                if (state === 'down') { count += 1; state = ''; }
                else { count -= state === 'up' ? 2 : 1; state = 'down'; }
            }
            box.dataset.voted = state;
            countEl.textContent = String(count);
            box.querySelector('.xf-vote-up').classList.toggle('text-success', state === 'up');
            box.querySelector('.xf-vote-down').classList.toggle('text-danger', state === 'down');
        });
    });

    // ---------- 待办清单 ----------
    XFAdmin.register('todo', function (el) {
        function recount() {
            var done = el.querySelectorAll('.xf-todo-done').length;
            var total = el.querySelectorAll('.xf-todo-list > li').length;
            var badge = el.querySelector('.xf-todo-count');
            if (badge) badge.textContent = done + '/' + total;
        }
        el.addEventListener('change', function (e) {
            var chk = e.target.closest('.xf-todo-check');
            if (!chk) return;
            chk.closest('li').classList.toggle('xf-todo-done', chk.checked);
            recount();
        });
        el.addEventListener('click', function (e) {
            var del = e.target.closest('.xf-todo-del');
            if (del) { del.closest('li').remove(); recount(); }
        });
        var addForm = el.querySelector('.xf-todo-add');
        if (addForm) {
            addForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var input = addForm.querySelector('input');
                var text = (input.value || '').trim();
                if (!text) return;
                var li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center gap-2 px-2 py-2';
                li.innerHTML = '<div class="form-check mb-0"><input class="form-check-input xf-todo-check" type="checkbox"></div>' +
                    '<span class="flex-grow-1"></span>' +
                    '<button type="button" class="btn btn-sm btn-link text-muted xf-todo-del" aria-label="删除"><i class="ti ti-x"></i></button>';
                li.querySelector('span').textContent = text;
                el.querySelector('.xf-todo-list').appendChild(li);
                input.value = '';
                recount();
            });
        }
        recount();
    });

    // 标签胶囊关闭（事件委托，一次绑定）
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-xf="chip-close"]');
        if (btn) {
            var chip = btn.closest('.badge');
            if (chip) { chip.dispatchEvent(new CustomEvent('xf.chip.close', { bubbles: true })); chip.remove(); }
        }
    });

    // 商品详情画廊缩略图切换（事件委托，多实例互不干扰）
    document.addEventListener('click', function (e) {
        var thumb = e.target.closest('.pd-thumb');
        if (!thumb) return;
        var root = thumb.closest('[data-pd-gallery]');
        if (!root) return;
        var main = root.querySelector('.pd-main');
        if (main && thumb.getAttribute('data-full')) {
            main.src = thumb.getAttribute('data-full');
        }
        root.querySelectorAll('.pd-thumb').forEach(function (t) { t.classList.remove('border-primary'); });
        thumb.classList.add('border-primary');
    });

    /* ------------------------------------------------------------------
     * 便捷 API
     * ---------------------------------------------------------------- */
    XFAdmin.toast = function (opts) {
        opts = opts || {};
        var container = document.querySelector('.xf-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'xf-toast-container toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = 1090;
            document.body.appendChild(container);
        }
        var variant = opts.variant || 'primary';
        var toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-bg-' + variant + ' border-0';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = '<div class="d-flex"><div class="toast-body"></div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
        toastEl.querySelector('.toast-body').textContent = opts.body || '';
        container.appendChild(toastEl);
        if (global.bootstrap && global.bootstrap.Toast) {
            var t = new global.bootstrap.Toast(toastEl, { delay: opts.delay || 4000 });
            toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });
            t.show();
        }
        return toastEl;
    };

    /* ------------------------------------------------------------------
     * Bootstrap 校验表单（非 AJAX）与 tooltip/popover 兜底初始化
     * ---------------------------------------------------------------- */
    function initBootstrapExtras(root) {
        root = root || document;
        Array.prototype.forEach.call(root.querySelectorAll('form.needs-validation:not([data-xf])'), function (form) {
            if (form.__xfValidated) return;
            form.__xfValidated = true;
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
        if (global.bootstrap) {
            Array.prototype.forEach.call(root.querySelectorAll('[data-bs-toggle="tooltip"]'), function (el) {
                if (!global.bootstrap.Tooltip.getInstance(el)) new global.bootstrap.Tooltip(el);
            });
            Array.prototype.forEach.call(root.querySelectorAll('[data-bs-toggle="popover"]'), function (el) {
                if (!global.bootstrap.Popover.getInstance(el)) new global.bootstrap.Popover(el);
            });
        }
    }

    /* ------------------------------------------------------------------
     * 启动
     * ---------------------------------------------------------------- */
    function boot() {
        XFAdmin.scan(document);
        initBootstrapExtras(document);
        XFAdmin.bindXfPageLinks(document);
        XFAdmin._readyQueue.forEach(function (fn) {
            try { fn(); } catch (e) { console.error('[XFAdmin] onReady', e); }
        });
        XFAdmin._readyQueue = [];
    }

    /* ------------------------------------------------------------------
     * 通用「页内弹窗链接」：[data-xf-page] 点击后用可最大化弹窗加载服务端页面。
     * 用于画廊/卡片/看板/会话等多样性视图中的查看入口，统一复用 editPage 弹窗。
     * ---------------------------------------------------------------- */
    XFAdmin.bindXfPageLinks = function (root) {
        root = root || document;
        Array.prototype.forEach.call(root.querySelectorAll('[data-xf-page]'), function (el) {
            if (el.__xfPageBound) return;
            el.__xfPageBound = true;
            el.addEventListener('click', function (e) {
                e.preventDefault();
                var url = el.getAttribute('data-xf-page');
                var title = el.getAttribute('data-xf-title') || '详情';
                if (!url) return;
                XFAdmin.pageDialog({ url: url, title: title, size: el.getAttribute('data-xf-size') || 'lg', maximizable: true, reload: false });
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    /* ------------------------------------------------------------------
     * 离线优先：外部图片加载失败时优雅降级为本地占位 SVG
     * 仅对 http(s) 外部资源生效；本地资源（/zxf/xfadmin、data:）不受影响。
     * 避免离线/外网被拦截时出现“破图”。
     * ---------------------------------------------------------------- */
    (function () {
        var PLACEHOLDER = 'data:image/svg+xml;utf8,' + encodeURIComponent(
            "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100'>" +
            "<rect width='100' height='100' fill='#e9ecef'/>" +
            "<g fill='#adb5bd'><circle cx='38' cy='36' r='9'/>" +
            "<path d='M22 74l24-30 16 20 11-13 14 17z'/></g></svg>"
        );
        document.addEventListener('error', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'IMG') return;
            var src = el.getAttribute('src') || '';
            if (/^(https?:)?\/\//i.test(src) && !el.dataset.xfImgFallback) {
                el.dataset.xfImgFallback = '1';
                el.src = PLACEHOLDER;
            }
        }, true);
    })();

    /* ------------------------------------------------------------------
     * 后台框架交互（主题 / 侧边栏 / 全屏 / 子菜单 / 定制面板）
     * ----------------------------------------------------------------
     * 【重要维护约定 · 勿回退】
     * 这些交互全部由 INSPINIA 原版 app.js 的 App / LayoutCustomizer /
     * ThemeCustomizer 负责，本包已原样内置并在 xfadmin.js 之前加载：
     *
     *   · .sidenav-toggle-button  -> LayoutCustomizer._toggleSidebar()
     *       依据 html[data-sidenav-size] 在 default/condensed/compact/
     *       offcanvas 间切换，offcanvas 态下创建 #custom-backdrop 遮罩，
     *       并联动 html.sidebar-enable。
     *   · .button-on-hover        -> on-hover / on-hover-active 切换
     *   · .button-close-offcanvas -> 移除 sidebar-enable + hideBackdrop()
     *   · #light-dark-mode        -> changeTheme() 并写入 __CONFIG__ 存储
     *   · .side-nav 子菜单        -> Bootstrap Collapse（data-bs-toggle=collapse），
     *       并做「同级互斥收起 + 按当前 URL 自动高亮父链 + 滚动定位」
     *   · 定制面板 input[name=data-*] -> LayoutCustomizer.initSwitchListener()
     *   · 响应式 _adjustLayout()  -> ≤767.98 强制 offcanvas，≤1140 强制 condensed
     *
     * 此前本文件重复实现了上述逻辑（用 body.sidenav-open + 自造 ::after 遮罩 +
     * .side-nav-item.open 手动展开 + 自己的 localStorage 键），与 app.js 的
     * 监听器【同时绑定在同一元素上】，一次点击触发两套互相抵消的状态机，
     * 表现为：菜单按钮点了没反应/闪一下复原、子菜单无动画、
     * 折叠态与模板不一致、主题偏好两套存储互相覆盖。
     *
     * 因此这里【不再绑定任何 chrome 事件】，仅做两件 app.js 未覆盖的补充：
     *   1) [data-toggle="fullscreen"] 全屏切换（模板 dist 未内置该绑定）；
     *   2) 兜底：当页面未引入 app.js（如独立组件文档页）时，
     *      才最小化启用侧边栏折叠，避免完全不可用。
     * ---------------------------------------------------------------- */
    (function () {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChrome);
        } else {
            initChrome();
        }

        function initChrome() {
            // 1) 全屏切换（app.js 未提供）
            Array.prototype.forEach.call(document.querySelectorAll('[data-toggle="fullscreen"]'), function (b) {
                b.addEventListener('click', function (e) {
                    e.preventDefault();
                    var el = document.documentElement;
                    var req = el.requestFullscreen || el.webkitRequestFullscreen;
                    var exit = document.exitFullscreen || document.webkitExitFullscreen;
                    if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                        if (req) req.call(el);
                    } else if (exit) {
                        exit.call(document);
                    }
                });
            });

            // 2) 兜底：仅在未加载 app.js 时接管侧边栏折叠，
            //    保证脱离完整后台布局的独立页面（如组件文档页）仍可用；有 app.js 时绝不介入。
            //
            // 注意：app.js 内的 App / LayoutCustomizer 等 class 是【脚本作用域】声明，
            // 不会挂到 window 上，因此不能用 typeof window.LayoutCustomizer 判断，
            // 否则恒为 undefined、兜底逻辑会与 app.js 重复绑定，一次点击触发两次
            // 切换而互相抵消（表现为“点击菜单按钮没反应”）。
            // 这里改用 app.js 启动后必然产生的 DOM 副作用来判定：
            // LayoutCustomizer.init() 会为 html 补上 data-sidenav-size 等布局属性。
            var htmlEl = document.documentElement;
            var appLoaded = htmlEl.hasAttribute('data-sidenav-size')
                || htmlEl.hasAttribute('data-layout')
                || typeof window.config !== 'undefined';
            if (appLoaded) return;

            Array.prototype.forEach.call(document.querySelectorAll('.sidenav-toggle-button'), function (b) {
                b.addEventListener('click', function (e) {
                    e.preventDefault();
                    var size = htmlEl.getAttribute('data-sidenav-size');
                    if (window.innerWidth <= 767.98) {
                        htmlEl.classList.toggle('sidebar-enable');
                        htmlEl.setAttribute('data-sidenav-size', 'offcanvas');
                    } else {
                        htmlEl.setAttribute('data-sidenav-size', size === 'condensed' ? 'default' : 'condensed');
                    }
                });
            });
            var closeBtn = document.querySelector('.button-close-offcanvas');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    htmlEl.classList.remove('sidebar-enable');
                });
            }
        }
    })();

    /* ------------------------------------------------------------------
     * TopNav 顶部水平导航
     *
     * 桌面端（≥992px）：级联展开完全由 CSS :hover 负责（框架 app.min.css 已实现），
     *   这里只做「边界避让」——子面板超出视口右侧时翻转到左侧。
     * 移动端（<992px）：菜单折叠进 collapse 面板，父项点击切换手风琴，
     *   需阻止 Bootstrap dropdown 的默认浮层定位。
     * ---------------------------------------------------------------- */
    (function () {
        var DESKTOP = 992;

        function ready(fn) {
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
            else fn();
        }

        function isDesktop() {
            return window.innerWidth >= DESKTOP;
        }

        ready(function () {
            var roots = document.querySelectorAll('.topnav-inline');
            if (!roots.length) return;

            Array.prototype.forEach.call(roots, function (root) {
                // ---- 移动端手风琴 ----
                // 必须用【捕获阶段】：Bootstrap 的 dropdown 在 document 上监听，
                // 冒泡阶段再 stopPropagation 已经晚了（浮层定位会覆盖手风琴状态）。
                root.addEventListener('click', function (e) {
                    if (isDesktop()) return;
                    var toggle = e.target.closest('.dropdown-toggle');
                    if (!toggle || !root.contains(toggle)) return;

                    e.preventDefault();
                    e.stopPropagation();
                    // 阻断同元素上其它监听器（含 Bootstrap 的委托处理）
                    if (e.stopImmediatePropagation) e.stopImmediatePropagation();

                    // 一级项 toggle 的父级是 <li.nav-item>，深层项是 <div.dropdown>
                    var parent = toggle.parentElement;
                    var menu = parent ? parent.querySelector(':scope > .dropdown-menu') : null;
                    if (!menu) return;

                    var willOpen = !menu.classList.contains('show');
                    // 收起同级其它分支
                    var siblings = parent.parentElement
                        ? parent.parentElement.querySelectorAll(':scope > * > .dropdown-menu.show')
                        : [];
                    Array.prototype.forEach.call(siblings, function (m) {
                        if (m !== menu) m.classList.remove('show');
                    });
                    menu.classList.toggle('show', willOpen);
                    toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                }, true);

                // ---- 桌面端边界避让 ----
                function place(menu) {
                    if (!menu || !isDesktop()) return;
                    menu.classList.remove('xf-flip-start', 'xf-flip-end');
                    var rect = menu.getBoundingClientRect();
                    var vw = document.documentElement.clientWidth;
                    if (rect.right <= vw - 4) return;
                    // 一级面板右对齐触发器，深层面板向左翻转
                    var isTop = menu.parentElement
                        && menu.parentElement.parentElement
                        && menu.parentElement.parentElement.classList.contains('navbar-nav');
                    menu.classList.add(isTop ? 'xf-flip-end' : 'xf-flip-start');
                }

                root.addEventListener('mouseover', function (e) {
                    if (!isDesktop()) return;
                    var item = e.target.closest('.dropdown');
                    if (!item || !root.contains(item)) return;
                    var menu = item.querySelector(':scope > .dropdown-menu');
                    if (menu) requestAnimationFrame(function () { place(menu); });
                });
            });

            // 视口切换时清理状态，避免移动端 .show 残留到桌面端
            var lastDesktop = isDesktop();
            window.addEventListener('resize', function () {
                var now = isDesktop();
                if (now === lastDesktop) return;
                lastDesktop = now;
                Array.prototype.forEach.call(
                    document.querySelectorAll('.topnav-inline .dropdown-menu'),
                    function (m) { m.classList.remove('show', 'xf-flip-start', 'xf-flip-end'); }
                );
            });
        });
    })();

    function onReady(fn) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
        else fn();
    }

    /* ==================== 弹窗页单元格渲染器（page） ====================
     * 列配置 'page' => '/user/{id}/edit' 或 ['url'=>..,'title'=>..,'size'=>..,'frame'=>..,'reload'=>..,'text'=>..,'icon'=>..]
     * 单元格值渲染为链接，点击弹窗加载后端页面；关闭弹窗后可选自动刷新本表（默认刷新）。
     * 复用声明式 data-xf-page-dialog 触发器，无需额外绑定。 */
    XFAdmin.cellRenderers.page = function (data, row, cfg, meta) {
        // URL 与标题均支持 {字段} 占位（取当前行数据）
        function fill(tpl, enc) {
            return String(tpl || '').replace(/\{(\w+)\}/g, function (m, k) {
                return row && row[k] != null ? (enc ? encodeURIComponent(row[k]) : String(row[k])) : m;
            });
        }
        var url = fill(cfg.url, true);
        var title = fill(cfg.title, false);
        var text = cfg.text != null ? fill(cfg.text, false) : (data == null ? '' : String(data));
        var tableId = '';
        try { tableId = meta.settings.nTable.id; } catch (e) { /* 非 DataTables 上下文 */ }
        return '<a href="javascript:void(0);" class="xf-cell-page link-primary text-decoration-underline-hover"' +
            ' data-xf-page-dialog="' + escapeHtml(url) + '"' +
            ' data-xf-title="' + escapeHtml(title || text) + '"' +
            ' data-xf-size="' + escapeHtml(cfg.size || 'lg') + '"' +
            (cfg.frame ? ' data-xf-frame' : '') +
            (tableId ? ' data-xf-table="#' + tableId + '"' : '') +
            (cfg.reload === false ? ' data-xf-reload="false"' : '') + '>' +
            (cfg.icon ? '<i class="' + escapeHtml(cfg.icon) + ' me-1"></i>' : '') + escapeHtml(text) + '</a>';
    };

    /* ==================== 弹窗子页 ↔ 父页面通信桥 ====================
     * 在 iframe 弹窗内加载的子页面中调用（同源）：
     *   XFAdmin.dialogBridge.close()            关闭弹窗
     *   XFAdmin.dialogBridge.closeAndReload()   关闭弹窗并刷新父页表格（或整页）
     *   XFAdmin.dialogBridge.markReload()       仅标记「关闭时需刷新」（不立即关闭）
     *   XFAdmin.dialogBridge.toast(msg, variant) 在父页面弹出提示
     *   XFAdmin.dialogBridge.inDialog           是否运行于弹窗 iframe 内
     * 非 iframe 环境下自动降级为本地行为，代码可两用。 */
    XFAdmin.dialogBridge = (function () {
        var inFrame = false;
        try { inFrame = global.self !== global.top; } catch (e) { inFrame = true; }
        function post(msg) {
            if (inFrame) { try { global.parent.postMessage(Object.assign({ __xf: 'dialog' }, msg), '*'); return true; } catch (e) { /* 跨域受限 */ } }
            return false;
        }
        return {
            inDialog: inFrame,
            close: function () { post({ action: 'close' }); },
            closeAndReload: function () { post({ action: 'reload-close' }); },
            markReload: function () { post({ action: 'reload' }); },
            toast: function (body, variant) {
                if (!post({ action: 'toast', body: String(body || ''), variant: variant || 'success' })) {
                    XFAdmin.toast({ body: String(body || ''), variant: variant || 'success' });
                }
            }
        };
    })();

    // 父页面：接收 iframe 子页的桥接消息（仅识别 __xf:'dialog' 协议，忽略其它 message）
    global.addEventListener('message', function (ev) {
        var d = ev && ev.data;
        if (!d || d.__xf !== 'dialog') return;
        if (d.action === 'toast') { XFAdmin.toast({ body: String(d.body || ''), variant: d.variant || 'success' }); return; }
        var modalEl = document.getElementById('xf-edit-modal');
        if (!modalEl) return;
        if (d.action === 'reload') modalEl.__xfNeedReload = true;
        if (d.action === 'close' || d.action === 'reload-close') {
            if (d.action === 'reload-close') modalEl.__xfNeedReload = true;
            var inst = global.bootstrap && global.bootstrap.Modal && global.bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide(); else modalEl.remove();
        }
    });

    /* ==================== 多语言 i18n（misc-i18.html） ====================
     * 翻译文件：resources/assets/data/translations/{code}.json（扁平键值对）。
     * 用法：HTML 元素加 data-lang="翻译键"；Topbar languages 配置带 code 即自动接线切换。
     *   XFAdmin.i18n.set('zh')   切换语言（自动拉取 JSON、应用并记忆到 localStorage）
     *   XFAdmin.i18n.set('')     恢复页面原始文案
     *   XFAdmin.i18n.t('key')    取当前语言翻译
     *   XFAdmin.i18n.apply(root) 对新插入的 DOM 片段应用当前语言 */
    XFAdmin.i18n = {
        lang: '',
        dict: {},
        base: null, // 翻译文件目录，默认由 xfadmin.js 的 script src 自动推导，可手动覆盖
        _resolveBase: function () {
            if (this.base) return this.base;
            var s = document.querySelector('script[src*="xfadmin.js"]');
            if (s && s.src) this.base = s.src.replace(/js\/xfadmin\.js.*$/, '') + 'data/translations/';
            return this.base || '';
        },
        t: function (key) { return Object.prototype.hasOwnProperty.call(this.dict, key) ? this.dict[key] : key; },
        apply: function (root) {
            var self = this;
            Array.prototype.forEach.call((root || document).querySelectorAll('[data-lang]'), function (el) {
                var key = el.getAttribute('data-lang');
                // 首次翻译前备份原始文案，便于切回默认语言
                if (!el.hasAttribute('data-xf-i18n-orig')) el.setAttribute('data-xf-i18n-orig', el.textContent);
                if (!self.lang) { el.textContent = el.getAttribute('data-xf-i18n-orig'); return; }
                if (Object.prototype.hasOwnProperty.call(self.dict, key)) el.textContent = self.dict[key];
            });
        },
        set: function (code) {
            var self = this;
            code = String(code || '');
            try { global.localStorage.setItem('xfadmin.lang', code); } catch (e) { /* 隐私模式 */ }
            if (!code) { self.lang = ''; self.dict = {}; self.apply(); return Promise.resolve(); }
            var base = self._resolveBase();
            return fetch(base + encodeURIComponent(code) + '.json').then(function (r) {
                return r.ok ? r.json() : {};
            }).then(function (dict) {
                self.lang = code;
                self.dict = dict || {};
                self.apply();
                try { document.dispatchEvent(new CustomEvent('xf:lang-changed', { detail: { lang: code } })); } catch (e) { /* noop */ }
            }).catch(function () { /* 翻译文件缺失时保持原文 */ });
        }
    };

    // Topbar 语言菜单接线（data-lang-code）+ 启动时恢复上次语言
    document.addEventListener('click', function (e) {
        if (!e.target || typeof e.target.closest !== 'function') return;
        var item = e.target.closest('[data-lang-code]');
        if (!item) return;
        e.preventDefault();
        XFAdmin.i18n.set(item.getAttribute('data-lang-code'));
    });
    (function () {
        var saved = '';
        try { saved = global.localStorage.getItem('xfadmin.lang') || ''; } catch (e) { /* noop */ }
        if (saved && document.querySelector('[data-lang]')) {
            onReady(function () { XFAdmin.i18n.set(saved); });
        }
    })();

    /* ==================== 数字滚动计数（metrics.html 指标卡） ====================
     * 元素：<span class="xf-count" data-xf-count="368425" data-xf-decimals="0">0</span>
     * 进入视口时开始计数（IntersectionObserver），千分位分隔，支持小数位。 */
    XFAdmin.countUp = function (el, target, opts) {
        opts = opts || {};
        var decimals = Number(opts.decimals || 0);
        var duration = Number(opts.duration || 1200);
        var start = null;
        function fmt(v) {
            var parts = v.toFixed(decimals).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            // easeOutCubic 缓动，结尾减速更自然
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(target * eased);
            if (p < 1) global.requestAnimationFrame(step);
        }
        global.requestAnimationFrame(step);
    };
    onReady(function () {
        var counters = document.querySelectorAll('[data-xf-count]');
        if (!counters.length) return;
        function run(el) {
            if (el.__xfCounted) return;
            el.__xfCounted = true;
            XFAdmin.countUp(el, parseFloat(el.getAttribute('data-xf-count')) || 0, {
                decimals: parseInt(el.getAttribute('data-xf-decimals') || '0', 10)
            });
        }
        if ('IntersectionObserver' in global) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) { if (en.isIntersecting) { run(en.target); io.unobserve(en.target); } });
            }, { threshold: 0.3 });
            Array.prototype.forEach.call(counters, function (el) { io.observe(el); });
        } else {
            Array.prototype.forEach.call(counters, run);
        }
    });

    /* ==================== 指标卡迷你图（metrics.html，基于 ECharts） ==================== */
    XFAdmin.register('metric-chart', function (el, cfg) {
        if (!global.echarts) return;
        cfg = cfg || {};
        var color = cfg.color || '#3e60d5';
        var data = Array.isArray(cfg.data) ? cfg.data : [];
        var type = cfg.type || 'donut';
        var isRound = type === 'donut' || type === 'pie';
        el.style.width = isRound ? '76px' : '110px';
        el.style.height = isRound ? '76px' : '56px';
        var option;
        var palette = [color, '#67b7dc', '#6794dc', '#8067dc', '#c767dc', '#dc67ce'];
        if (isRound) {
            option = {
                color: palette,
                series: [{
                    type: 'pie',
                    radius: type === 'donut' ? ['62%', '92%'] : '92%',
                    label: { show: false },
                    data: data.map(function (v, i) { return { value: v, name: (cfg.labels && cfg.labels[i]) || ('项' + (i + 1)) }; })
                }],
                tooltip: { show: true, confine: true }
            };
        } else if (type === 'bar') {
            option = {
                grid: { left: 0, right: 0, top: 2, bottom: 2 },
                xAxis: { type: 'category', show: false, data: data.map(function (_, i) { return i + 1; }) },
                yAxis: { type: 'value', show: false },
                series: [{ type: 'bar', data: data, itemStyle: { color: color, borderRadius: [2, 2, 0, 0] }, barWidth: '55%' }],
                tooltip: { show: false }
            };
        } else { // area / line
            option = {
                grid: { left: 0, right: 0, top: 2, bottom: 2 },
                xAxis: { type: 'category', show: false, data: data.map(function (_, i) { return i + 1; }) },
                yAxis: { type: 'value', show: false },
                series: [{
                    type: 'line', data: data, symbol: 'none', smooth: true,
                    lineStyle: { color: color, width: 2 },
                    areaStyle: type === 'area' ? { color: color, opacity: 0.18 } : undefined
                }],
                tooltip: { show: false }
            };
        }
        var chart = global.echarts.init(el);
        chart.setOption(option);
        return chart;
    });

    /* ==================== 组织架构树（charts-apextree.html，apextree 插件） ==================== */
    XFAdmin.register('apextree', function (el, cfg) {
        if (!global.ApexTree) return;
        cfg = cfg || {};
        // 把扁平节点（name/role/avatar/color）转换为 apextree 期望的 {id,data,options,children} 结构
        function toNode(n, idx) {
            n = n || {};
            var node = {
                id: String(n.id != null ? n.id : 'n' + idx),
                data: { name: n.name || '', role: n.role || '', avatar: n.avatar || '' },
                options: n.color ? { nodeBGColor: '#fff', nodeBGColorHover: '#fff', borderColor: n.color } : undefined
            };
            if (Array.isArray(n.children) && n.children.length) {
                node.children = n.children.map(function (c, i) { return toNode(c, idx + '-' + i); });
            }
            return node;
        }
        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var tree = new global.ApexTree(el, {
            contentKey: 'data',
            width: el.clientWidth || '100%',
            height: cfg.height || 500,
            nodeWidth: cfg.nodeWidth || 150,
            nodeHeight: cfg.nodeHeight || 60,
            childrenSpacing: 40,
            siblingSpacing: 24,
            direction: cfg.direction || 'top',
            enableToolbar: true,
            canvasStyle: 'border-radius:8px;background:' + (isDark ? '#1e2530' : '#f8f9fb') + ';',
            edgeColor: '#c8cdd8',
            edgeColorHover: '#3e60d5',
            nodeTemplate: function (d) {
                var avatar = d.avatar ? '<img src="' + escapeHtml(d.avatar) + '" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">' : '';
                return '<div class="d-flex align-items-center gap-2 justify-content-center h-100 px-2">' + avatar +
                    '<div class="text-start overflow-hidden"><div class="fw-semibold text-truncate fs-13">' + escapeHtml(d.name || '') + '</div>' +
                    (d.role ? '<div class="text-muted text-truncate" style="font-size:11px">' + escapeHtml(d.role) + '</div>' : '') + '</div></div>';
            }
        });
        tree.render(toNode(cfg.data, 0));
        // 登记主题热切换重建器：重新渲染即读取最新 data-bs-theme
        window.__xfApexSpecial = window.__xfApexSpecial || [];
        window.__xfApexSpecial.push({
            rebuild: function () {
                try {
                    var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    tree.updateConfig({ canvasStyle: 'border-radius:8px;background:' + (dark ? '#1e2530' : '#f8f9fb') + ';' });
                    tree.render(toNode(cfg.data, 0));
                } catch (e) {}
            }
        });
        return tree;
    });

    /* ==================== 桑基图（charts-apexsankey.html，apexsankey 插件 + svg.js） ====================
     * 配置由 PHP 端 ApexSankey 组件输出：
     *   nodes: [{id,title,color?}]  edges: [{source,target,value,color?}]
     *   height / nodeWidth / toolbar / order（各列节点排序）/ options（原生图形配置透传，最高优先级）
     */
    XFAdmin.register('apexsankey', function (el, cfg) {
        if (!global.ApexSankey) return;
        cfg = cfg || {};
        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        // 图形配置：内置暗色适配的默认值，cfg.options 可整体覆盖任意原生项
        var graphOpts = Object.assign({
            width: '100%',
            height: cfg.height || 400,
            nodeWidth: cfg.nodeWidth || 20,
            fontFamily: getComputedStyle(document.body).fontFamily,
            fontSize: '13px',
            fontWeight: 500,
            fontColor: isDark ? '#aab8c5' : '#5d7186',
            enableToolbar: cfg.toolbar !== false,
            canvasStyle: 'none'
        }, cfg.options || {});
        // 数据结构：{nodes, edges, options:{order}}（order 控制每一列节点的显示顺序）
        var data = { nodes: cfg.nodes || [], edges: cfg.edges || [] };
        if (cfg.order) data.options = { order: cfg.order };
        var chart = new global.ApexSankey(el, graphOpts);
        chart.render(data);
        // 登记主题热切换重建器：fontColor 随 data-bs-theme 变化
        window.__xfApexSpecial = window.__xfApexSpecial || [];
        window.__xfApexSpecial.push({
            rebuild: function () {
                try {
                    var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    chart.updateConfig(Object.assign({}, graphOpts, { fontColor: dark ? '#aab8c5' : '#5d7186' }));
                    chart.render(data);
                } catch (e) {}
            }
        });
        return chart;
    });

    /* ==================== CSS 动画触发器（misc-animation.html，animate.css） ====================
     * data-xf="animate" + data-xf-animation="bounce" + data-xf-trigger="hover|click|scroll"
     * load 触发由 PHP 端直接输出动画类；此处负责 hover/click/scroll 三种延迟触发，
     * 且 animationend 后移除类，保证可重复播放。 */
    XFAdmin.register('animate', function (el, _cfg) {
        var anim = el.getAttribute('data-xf-animation') || 'bounce';
        var trigger = el.getAttribute('data-xf-trigger') || 'load';
        var cls = ['animate__animated', 'animate__' + anim];
        function play() {
            if (el.classList.contains('animate__animated')) return;
            cls.forEach(function (c) { el.classList.add(c); });
        }
        // 非无限循环时播放完移除动画类，允许再次触发
        el.addEventListener('animationend', function () {
            if (!el.classList.contains('animate__infinite')) {
                cls.forEach(function (c) { el.classList.remove(c); });
            }
        });
        if (trigger === 'hover') {
            el.addEventListener('mouseenter', play);
        } else if (trigger === 'click') {
            el.addEventListener('click', play);
        } else if (trigger === 'scroll') {
            if ('IntersectionObserver' in global) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (en) { if (en.isIntersecting) { play(); io.unobserve(en.target); } });
                }, { threshold: 0.25 });
                io.observe(el);
            } else {
                play();
            }
        }
    });

    /* ------------------------------------------------------------------
     * 倒计时 / 数字滚动 / 返回顶部
     * ------------------------------------------------------------------ */
    XFAdmin.register('countdown', function (el, cfg) {
        var labels = cfg.labels || ['天', '时', '分', '秒'];
        var expired = cfg.expired || '已结束';
        function targetMs() {
            var t = cfg.target;
            if (t === '' || t == null) return null;
            if (typeof t === 'number') return t < 1e12 ? t * 1000 : t; // 秒/毫秒自适应
            var d = Date.parse(t);
            return isNaN(d) ? null : d;
        }
        var end = targetMs();
        var nums = {};
        el.querySelectorAll('.xf-cd-num').forEach(function (n) { nums[n.getAttribute('data-u')] = n; });
        function pad(n) { return (n < 10 ? '0' : '') + n; }
        function tick() {
            if (end == null) { el.innerHTML = '<span class="text-muted">' + expired + '</span>'; return; }
            var diff = end - Date.now();
            if (diff <= 0) {
                el.innerHTML = '<span class="text-muted">' + expired + '</span>';
                clearInterval(timer);
                return;
            }
            var s = Math.floor(diff / 1000);
            var d = Math.floor(s / 86400); s -= d * 86400;
            var h = Math.floor(s / 3600); s -= h * 3600;
            var m = Math.floor(s / 60); s -= m * 60;
            if (nums.d) nums.d.textContent = pad(d);
            if (nums.h) nums.h.textContent = pad(h);
            if (nums.m) nums.m.textContent = pad(m);
            if (nums.s) nums.s.textContent = pad(s);
        }
        tick();
        var timer = setInterval(tick, 1000);
    });

    XFAdmin.register('countup', function (el, cfg) {
        var value = Number(cfg.value) || 0;
        var decimals = parseInt(cfg.decimals, 10) || 0;
        var duration = parseInt(cfg.duration, 10) || 1500;
        var prefix = cfg.prefix || '', suffix = cfg.suffix || '';
        function fmt(n) {
            return prefix + (decimals > 0 ? n.toFixed(decimals) : Math.round(n).toString()) + suffix;
        }
        function run() {
            var start = null;
            function step(ts) {
                if (start == null) start = ts;
                var p = Math.min(1, (ts - start) / duration);
                var eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
                el.textContent = fmt(value * eased);
                if (p < 1) requestAnimationFrame(step);
                else el.textContent = fmt(value);
            }
            requestAnimationFrame(step);
        }
        if ('IntersectionObserver' in global) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) { run(); io.unobserve(en.target); }
                });
            }, { threshold: 0.2 });
            io.observe(el);
        } else { run(); }
    });

    XFAdmin.register('backtotop', function (el, cfg) {
        var offset = parseInt(cfg.offset, 10) || 300;
        el.style.display = 'none';
        el.addEventListener('click', function () {
            if (global.scrollTo) global.scrollTo({ top: 0, behavior: 'smooth' });
            else global.scrollTo(0, 0);
        });
        function onScroll() {
            var y = global.pageYOffset || global.scrollY || document.documentElement.scrollTop || 0;
            el.style.display = y > offset ? 'block' : 'none';
        }
        global.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });

    XFAdmin.register('codeCopy', function (el) {
        var block = el.closest('.xf-code-block');
        if (!block) return;
        var code = block.querySelector('code');
        var text = code ? code.textContent : '';
        function flash(msg) {
            var old = el.textContent;
            el.textContent = msg;
            setTimeout(function () { el.textContent = old; }, 1200);
        }
        if (global.navigator && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { flash('已复制'); }, function () { flash('复制失败'); });
        } else {
            try {
                var ta = document.createElement('textarea');
                ta.value = text; document.body.appendChild(ta); ta.select();
                document.execCommand('copy'); document.body.removeChild(ta);
                flash('已复制');
            } catch (e) { flash('复制失败'); }
        }
    });

    /* ------------------------------------------------------------------
     * 页面加载动画（preloader）隐藏
     * ----------------------------------------------------------------
     * 结构见 Page 组件：<div id="preloader">…</div>。
     * window load 后淡出；另设 3s 兜底超时——若外部资源（字体/瓦片等）
     * 被拦截导致 load 永不触发，仍能及时移除遮罩，避免页面永久被盖住。 */
    function hidePreloader() {
        var el = document.getElementById('preloader');
        if (!el || el.dataset.xfHidden) return;
        el.dataset.xfHidden = '1';
        el.style.opacity = '0';
        el.style.transition = 'opacity .35s ease';
        setTimeout(function () { el.remove(); }, 380);
    }
    if (document.getElementById('preloader')) {
        global.addEventListener('load', hidePreloader);
        setTimeout(hidePreloader, 3000);
    }

    global.XFAdmin = XFAdmin;
})(window);
