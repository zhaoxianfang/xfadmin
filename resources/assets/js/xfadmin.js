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
        return fetch(url, init).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                var result = { ok: res.ok, status: res.status, data: data };
                if (!res.ok && !opts.silent) {
                    XFAdmin.toast({ body: (data && data.message) || '请求失败(' + res.status + ')', variant: 'danger' });
                }
                return result;
            });
        }).catch(function (err) {
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
        paginate: { first: '首页', previous: '上一页', next: '下一页', last: '末页' },
        aria: { sortAscending: ': 升序排列', sortDescending: ': 降序排列' },
        select: { rows: { _: '已选择 %d 行', 0: '', 1: '已选择 1 行' } },
        buttons: { copy: '复制', csv: 'CSV', excel: 'Excel', print: '打印', pdf: 'PDF', colvis: '列显示', copyTitle: '已复制到剪贴板', copySuccess: { _: '已复制 %d 行', 1: '已复制 1 行' } }
    };

    /* ------------------------------------------------------------------
     * DataTable 富单元格渲染器（可通过 XFAdmin.cellRenderers.xxx 扩展）
     * 每个渲染器：function (data, row, cfg, meta) => html 字符串
     * ---------------------------------------------------------------- */
    function str(v) { return v == null ? '' : String(v); }

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

        /* 行操作栏：cfg.items = [{label, icon, class, url, ajax, method, confirm, reload, action, dropdown}] */
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
                if (item.url) {
                    return '<a href="' + XFAdmin.tpl(item.url, row) + '"' + attrs + (item.target ? ' target="' + escapeHtml(item.target) + '"' : '') + '>' + inner + '</a>';
                }
                attrs += ' data-xf-act="' + escapeHtml(item.action || 'ajax') + '"';
                if (item.ajax) attrs += ' data-xf-url="' + escapeHtml(XFAdmin.tpl(item.ajax, row)) + '"';
                if (item.method) attrs += ' data-xf-method="' + escapeHtml(item.method) + '"';
                if (item.confirm) attrs += ' data-xf-confirm="' + escapeHtml(item.confirm) + '"';
                if (item.reload) attrs += ' data-xf-reload="1"';
                if (item.event) attrs += ' data-xf-event="' + escapeHtml(item.event) + '"';
                return '<button type="button"' + attrs + '>' + inner + '</button>';
            }
        }
    };

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
        // Lucide 图标（增量渲染）
        if (global.lucide && typeof global.lucide.createIcons === 'function') {
            try { global.lucide.createIcons(); } catch (e) { /* noop */ }
        }
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

        // 缺省中文语言包（配置传入的 language 优先）
        dt.language = Object.assign({}, XFAdmin.dtLanguage, dt.language || {});

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
            }
            if (col.xfRender) {
                var cfg = col.xfRender;
                var renderer = XFAdmin.cellRenderers[cfg.type] || XFAdmin.cellRenderers.text;
                col.render = function (data, type, row, meta) {
                    if (type && type !== 'display') return cfg.type === 'actions' ? '' : data;
                    return renderer(data, row || {}, cfg, meta);
                };
                delete col.xfRender;
            }
        });

        // 包内扩展按钮（refresh / fullscreen）
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
            }
        });

        // AJAX 错误优雅提示（阻止 DataTables 原生 alert 弹窗）
        try { global.DataTable.ext.errMode = 'none'; } catch (e) { /* noop */ }
        el.addEventListener('error.dt', function () {
            XFAdmin.toast({ body: '表格数据加载失败，请检查网络或稍后重试', variant: 'danger' });
        });
        if (global.jQuery) {
            global.jQuery(el).on('error.dt', function (e, settings, techNote, message) {
                console.warn('[XfAdmin] DataTables:', message);
                XFAdmin.toast({ body: '表格数据加载失败，请稍后重试', variant: 'danger' });
            });
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
                var reloadWithFilters = function () {
                    var qs = [];
                    bar.querySelectorAll('.xf-filter').forEach(function (c) {
                        if (c.value) qs.push(encodeURIComponent(c.dataset.filter) + '=' + encodeURIComponent(c.value));
                    });
                    bar.querySelectorAll('.xf-filter-radio').forEach(function (g) {
                        var activeBtn = g.querySelector('.active');
                        var v = activeBtn ? activeBtn.dataset.value : '';
                        if (v) qs.push(encodeURIComponent(g.dataset.filter) + '=' + encodeURIComponent(v));
                    });
                    if (baseUrl) {
                        table.ajax.url(baseUrl + (qs.length ? '?' + qs.join('&') : '')).load();
                    } else {
                        table.draw();
                    }
                };
                var filterTimer = null;
                bar.querySelectorAll('.xf-filter').forEach(function (c) {
                    c.addEventListener('change', reloadWithFilters);
                    if (c.type === 'text') {
                        c.addEventListener('input', function () {
                            clearTimeout(filterTimer);
                            filterTimer = setTimeout(reloadWithFilters, 400);
                        });
                        c.addEventListener('keyup', function (e) { if (e.key === 'Enter') reloadWithFilters(); });
                    }
                });
                bar.querySelectorAll('.xf-filter-radio').forEach(function (g) {
                    g.addEventListener('click', function (e) {
                        var btn = e.target.closest('[data-value]');
                        if (!btn) return;
                        g.querySelectorAll('.btn').forEach(function (b) { b.classList.remove('active'); });
                        btn.classList.add('active');
                        reloadWithFilters();
                    });
                });
                var resetBtn = bar.querySelector('.xf-filter-reset');
                if (resetBtn) {
                    resetBtn.addEventListener('click', function () {
                        bar.querySelectorAll('.xf-filter').forEach(function (c) { c.value = ''; });
                        bar.querySelectorAll('.xf-filter-radio').forEach(function (g) {
                            g.querySelectorAll('.btn').forEach(function (b, i) { b.classList.toggle('active', i === 0); });
                        });
                        reloadWithFilters();
                    });
                }
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
            XFAdmin.viewRow(row);
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
    });

    /* 确认弹窗（Bootstrap Modal，无 Modal 时回退原生 confirm） */
    XFAdmin.confirm = function (message, onOk, onCancel) {
        if (!global.bootstrap || !global.bootstrap.Modal) {
            if (window.confirm(message)) { onOk && onOk(); } else { onCancel && onCancel(); }
            return;
        }
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
        modalEl.querySelector('.xf-confirm-msg').textContent = message;
        var modal = global.bootstrap.Modal.getOrCreateInstance(modalEl);
        var okBtn = modalEl.querySelector('.xf-confirm-ok');
        var newOk = okBtn.cloneNode(true);
        okBtn.parentNode.replaceChild(newOk, okBtn);
        newOk.addEventListener('click', function () {
            modal.hide();
            onOk && onOk();
        });
        modal.show();
    };

    /* 行详情弹窗（键值对表格展示） */
    XFAdmin.viewRow = function (row, title) {
        row = row || {};
        var body = '<table class="table table-sm table-striped mb-0"><tbody>';
        Object.keys(row).forEach(function (k) {
            var v = row[k];
            if (v !== null && typeof v === 'object') v = JSON.stringify(v);
            body += '<tr><th class="text-muted" style="width:30%">' + escapeHtml(k) + '</th><td class="text-break">' + escapeHtml(v) + '</td></tr>';
        });
        body += '</tbody></table>';
        XFAdmin.dialog({ title: title || '详细信息', body: body, size: 'lg' });
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
            '<div class="modal-body">' + (opts.body || '') + '</div></div></div>';
        document.body.appendChild(modalEl);
        var modal = new global.bootstrap.Modal(modalEl);
        modalEl.addEventListener('hidden.bs.modal', function () { modalEl.remove(); });
        modal.show();
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
        }
    });

    // ---------- 图表 ----------
    XFAdmin.register('apexchart', function (el, options) {
        if (!global.ApexCharts) return;
        var chart = new global.ApexCharts(el, options);
        chart.render();
        return chart;
    });

    XFAdmin.register('echart', function (el, config) {
        if (!global.echarts) return;
        var chart = global.echarts.init(el, config.theme || null);
        chart.setOption(config.options || {});
        // 全局仅绑定一次 resize，统一重绘所有已注册的 ECharts 实例（避免重复渲染叠加监听）
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
        var opts = Object.assign({}, config);
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

    XFAdmin.register('calendar', function (el, config) {
        if (!global.FullCalendar) return;
        var calendar = new global.FullCalendar.Calendar(el, config);
        calendar.render();
        return calendar;
    });

    XFAdmin.register('tour', function (el, config) {
        if (!global.tourguide) return;
        var tg = new global.tourguide.TourGuideClient(Object.assign({ steps: config.steps }, config.options || {}));
        if (config.auto) tg.start();
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

    // ---------- 看板拖拽 ----------
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
        return instances;
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

    // 标签胶囊关闭（事件委托，一次绑定）
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-xf="chip-close"]');
        if (btn) {
            var chip = btn.closest('.badge');
            if (chip) { chip.dispatchEvent(new CustomEvent('xf.chip.close', { bubbles: true })); chip.remove(); }
        }
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

    XFAdmin.confirm = function (opts) {
        opts = opts || {};
        if (global.Swal) {
            return global.Swal.fire({
                title: opts.title || '确认操作？',
                text: opts.text || '',
                icon: opts.icon || 'question',
                showCancelButton: true,
                confirmButtonText: opts.confirmText || '确定',
                cancelButtonText: opts.cancelText || '取消',
                customClass: { confirmButton: 'btn btn-primary me-2 mt-2', cancelButton: 'btn btn-light mt-2' },
                buttonsStyling: false
            }).then(function (r) { return r.isConfirmed; });
        }
        return Promise.resolve(window.confirm(opts.title || '确认操作？'));
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
        XFAdmin._readyQueue.forEach(function (fn) {
            try { fn(); } catch (e) { console.error('[XFAdmin] onReady', e); }
        });
        XFAdmin._readyQueue = [];
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    global.XFAdmin = XFAdmin;
})(window);
