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

    // ---------- DataTable（含模板列 / 徽章列 / 列筛选） ----------
    XFAdmin.register('datatable', function (el, config) {
        if (!global.DataTable) return;
        var dt = config.dt || {};

        (dt.columns || []).forEach(function (col) {
            if (col.xfTemplate) {
                var tpl = col.xfTemplate;
                col.render = function (data, type, row) {
                    return tpl.replace(/\{(\w+(?:\.\w+)*)\}/g, function (_, path) {
                        var v = path.split('.').reduce(function (o, k) {
                            return o == null ? '' : o[k];
                        }, row);
                        return escapeHtml(v);
                    });
                };
                if (col.orderable === undefined) col.orderable = false;
                delete col.xfTemplate;
            }
            if (col.xfBadges) {
                var badges = col.xfBadges;
                col.render = function (data) {
                    var variant = badges[data];
                    var safe = escapeHtml(data);
                    return variant ? '<span class="badge bg-' + variant + '-subtle text-' + variant + '">' + safe + '</span>' : safe;
                };
                delete col.xfBadges;
            }
        });

        var table = new global.DataTable(el, dt);

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
                input.addEventListener('input', function () {
                    table.column(idx).search(this.value).draw();
                });
                td.appendChild(input);
                filterRow.appendChild(td);
            });
            el.querySelector('thead').appendChild(filterRow);
        }
        return table;
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

            fetch(form.action || window.location.href, {
                method: form.getAttribute('method') || 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
