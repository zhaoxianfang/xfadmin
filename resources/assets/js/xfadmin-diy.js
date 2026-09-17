/*!
 * XfAdmin · DIY 可视化布局器（DiyLayoutPage）编辑器运行时
 * 纯原生 JS（无 jQuery 依赖），与 XFAdmin.scan / XFAdmin.load 协同完成动态预览。
 *
 * 设计对标 Bootstrap LayoutIt：
 *   - 左侧：全部已注册组件（按分类）+ 内置模板 + 布局元素（行/列），可拖拽或点击添加；
 *   - 中间：布局舞台（行/列可嵌套），拖入组件即向服务端请求实时预览；
 *   - 右侧：选中块（组件/行/列）的属性 / 配置 / 数据编辑面板（字段由组件 defaults() 自动生成）。
 *
 * 增强能力：设备预览（桌面/平板/手机）、撤销/重做、复制/粘贴、全屏预览浮层、
 *           localStorage 自动保存、键盘快捷键、智能属性编辑器（颜色/图标/图片/链接/变体）。
 * 渲染约定：编辑态舞台每次改动都会整体重渲染，故图表/地图类组件在编辑态以静态占位呈现，
 *           仅在「预览浮层 / 导出页」按真实配置渲染（由 window.__xfDiyEdit 标记切换）。
 */
(function () {
    'use strict';

    var CAT_ICON = {
        Layout: 'ti ti-layout', Navigation: 'ti ti-menu-2', Grid: 'ti ti-layout-grid',
        UI: 'ti ti-shapes', Form: 'ti ti-forms', Chart: 'ti ti-chart-bar',
        Table: 'ti ti-table', Data: 'ti ti-database', Misc: 'ti ti-dots'
    };

    function init() {
        var XF = window.XFAdmin;
        window.__xfDiyEdit = true; // 编辑器内图表/地图以占位呈现；预览浮层扫描时临时置 false 真实渲染
        if (!XF) { return; }
        if (!XF._xfDiyRegistered && XF.register) {
            XF._xfDiyRegistered = true;
            XF.register('diy', function (el) { setup(el, XF); });
        }
        var editors = document.querySelectorAll('.xf-diy-editor');
        for (var i = 0; i < editors.length; i++) {
            try { setup(editors[i], XF); } catch (e) { console.error('[DiyLayoutPage]', e); }
        }
    }

    function setup(root, XF) {
        // 防止 init() 与 XFAdmin.register 回调重复初始化（否则会叠加事件监听、重复渲染）
        if (root.getAttribute('data-xf-diy-inited')) { return; }
        root.setAttribute('data-xf-diy-inited', '1');

        var cfg;
        try { cfg = JSON.parse(root.getAttribute('data-xf-config')); }
        catch (e) { console.error('[DiyLayoutPage] data-xf-config 解析失败', e); cfg = {}; }

        var state = {
            root: root, XF: XF,
            renderUrl: cfg.render_url || '/diy/render',
            components: {},
            templates: cfg.templates || [],
            tree: cfg.blocks || [],
            byId: {},
            selected: null,
            seq: 1,
            history: [''], histIndex: 0,
            clipboard: null,
            collapsed: {},
            previewCache: {},
            zoom: 100,
            devWidth: { desktop: null, tablet: 834, mobile: 390 }
        };
        (cfg.components || []).forEach(function (c) { state.components[c.alias] = c; });

        var stage = root.querySelector('#xfDiyStage');
        var props = root.querySelector('#xfDiyProps');
        var palette = root.querySelector('.xf-diy-palette');
        var compList = root.querySelector('.xf-diy-comp-list');
        var search = root.querySelector('.xf-diy-search');
        var catFilter = root.querySelector('.xf-diy-cat-filter');

        state.stage = stage;
        state.props = props;
        state.compList = compList;

        // ---------- 初始块树（优先级：本地草稿 > 服务端初始 blocks；默认空舞台）----------
        var saved = loadLocal();
        if (saved !== null && Array.isArray(saved)) {
            state.tree = saved; // 已清空则为 []；否则为上次草稿
        }
        // 否则使用 cfg.blocks（setup 已写入 state.tree），即空舞台，全部由用户自定义
        var SAMPLE_TREE = cfg.sample || [];

        // ---------- 左侧组件列表（按分类分组、可折叠、带计数）----------
        function renderPalette() {
            var q = (search ? search.value : '').trim().toLowerCase();
            var cat = catFilter ? catFilter.value : '';
            var groups = {};
            var order = [];
            (cfg.components || []).forEach(function (c) {
                if (cat && c.category !== cat) { return; }
                if (q && c.label.toLowerCase().indexOf(q) === -1 && c.alias.toLowerCase().indexOf(q) === -1) { return; }
                if (!groups[c.category]) { groups[c.category] = []; order.push(c.category); }
                groups[c.category].push(c);
            });
            var catOrder = (cfg.categories && Object.keys(cfg.categories)) || order;
            var html = '';
            if (!order.length) {
                html = '<div class="text-muted small py-3 text-center">无匹配组件</div>';
            } else {
                catOrder.forEach(function (key) {
                    if (!groups[key]) { return; }
                    var items = groups[key];
                    var ico = CAT_ICON[key] || 'ti ti-puzzle';
                    var label = (cfg.categories && cfg.categories[key]) ? cfg.categories[key] : key;
                    var collapsed = state.collapsed[key] ? ' collapsed' : '';
                    html += '<div class="xf-diy-cat' + collapsed + '" data-cat="' + esc(key) + '">'
                        + '<button type="button" class="xf-diy-cat-head" data-cat-toggle="' + esc(key) + '">'
                        + '<i class="' + ico + ' xf-diy-cat-ico"></i><span class="xf-diy-cat-label">' + esc(label) + '</span>'
                        + '<span class="xf-diy-cat-count">' + items.length + '</span>'
                        + '<i class="ti ti-chevron-down xf-diy-cat-caret"></i></button>'
                        + '<div class="xf-diy-cat-body">';
                    items.forEach(function (c) {
                        html += '<div class="xf-diy-comp-item" draggable="true" data-alias="' + esc(c.alias) + '" '
                            + 'title="拖拽或点击添加到舞台：' + esc(c.label) + '">'
                            + '<i class="xf-diy-comp-ico ' + ico + '"></i>'
                            + '<span class="xf-diy-comp-name">' + esc(c.label) + '</span>'
                            + '<span class="xf-diy-comp-cat">' + esc(c.alias) + '</span></div>';
                    });
                    html += '</div></div>';
                });
            }
            compList.innerHTML = html;
        }
        renderPalette();
        if (search) { search.addEventListener('input', renderPalette); }
        if (catFilter) { catFilter.addEventListener('change', renderPalette); }
        // 分类折叠
        compList.addEventListener('click', function (e) {
            var h = e.target.closest ? e.target.closest('[data-cat-toggle]') : null;
            if (h && !(e.target.closest('.xf-diy-comp-item'))) {
                var key = h.getAttribute('data-cat-toggle');
                state.collapsed[key] = !state.collapsed[key];
                var sec = h.closest('.xf-diy-cat');
                if (sec) { sec.classList.toggle('collapsed'); }
            }
        });

        // 组件项 / 布局元素：点击即追加（拖拽的等价快捷方式），并尊重当前选中块作为放置目标
        compList.addEventListener('click', function (e) {
            var it = e.target.closest ? e.target.closest('.xf-diy-comp-item') : null;
            if (!it) { return; }
            var alias = it.getAttribute('data-alias');
            var kind = it.getAttribute('data-diy-kind');
            if (alias) { addBySelection('component', alias); }
            else if (kind) { addBySelection(kind); }
        });
        var layoutList = palette.querySelector('.xf-diy-layout-list');
        if (layoutList) {
            layoutList.addEventListener('click', function (e) {
                var it = e.target.closest ? e.target.closest('.xf-diy-comp-item') : null;
                if (it && it.getAttribute('data-diy-kind')) { addBySelection(it.getAttribute('data-diy-kind')); }
            });
        }

        // ---------- 工具 ----------
        function newId() { return 'b' + (state.seq++); }
        function lsKey() { return 'xfdiy:' + state.renderUrl; }
        function saveLocal() { try { localStorage.setItem(lsKey(), JSON.stringify(state.tree)); } catch (e) {} }
        function loadLocal() { try { var s = localStorage.getItem(lsKey()); return s ? JSON.parse(s) : null; } catch (e) { return null; } }

        function csrf() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute('content') : '';
        }

        function fetchRender(payload) {
            return fetch(state.renderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify(payload)
            }).then(function (r) { return r.json(); });
        }

        // 组件渲染失败时 / 空内容时的占位，保证块在舞台中可见且可点击编辑
        function placeholderFor(block) {
            var label = (state.components[block.alias] && state.components[block.alias].label) || block.alias;
            return '<div class="xf-diy-empty-preview"><i class="ti ti-pencil"></i><span>' + esc(label) + '（在右侧编辑属性以填充内容）</span></div>';
        }

        function loadPreview(block, bodyEl) {
            var sel = '.xf-diy-block[data-block-id="' + cssEsc(block.id) + '"] .xf-diy-block-body';
            // bodyEl 直接指向本块 .xf-diy-block-body（构建阶段已持有，避免「元素尚未挂载到 DOM 即查询」的竞态）
            function targetEl() { return bodyEl || stage.querySelector(sel); }
            var cacheKey = block.alias + '|' + JSON.stringify(block.options || {});
            // 命中缓存：直接复用上次渲染结果，避免结构变动时全量重渲染导致的请求风暴与闪烁
            if (state.previewCache[cacheKey] !== undefined) {
                var cached = state.previewCache[cacheKey];
                var cel = targetEl();
                if (cel) { cel.innerHTML = cached; try { XF.scan(cel); } catch (e) {} }
                return Promise.resolve();
            }
            return fetchRender({ mode: 'block', alias: block.alias, options: block.options || {} })
                .then(function (res) {
                    var css = (res && res.css) || [];
                    var js = (res && res.js) || [];
                    var done = function () {
                        var el = targetEl();
                        if (!el) { return; } // 已被后续渲染替换，丢弃
                        var html = (res && res.html != null) ? res.html : '';
                        var finalHtml = html.trim() ? html : placeholderFor(block);
                        state.previewCache[cacheKey] = finalHtml; // 记入缓存
                        el.innerHTML = finalHtml;
                        try { XF.scan(el); } catch (e) {}
                    };
                    if ((css.length || js.length) && XF.load) {
                        return XF.load(css, js).then(done, done);
                    }
                    done();
                })
                .catch(function (e) {
                    var el = targetEl();
                    if (el) { el.innerHTML = '<div class="alert alert-danger mb-0">预览渲染失败：' + esc(String(e)) + '</div>'; }
                });
        }

        // 构建单个块 DOM
        function buildBlock(block) {
            var wrap = document.createElement('div');
            wrap.className = 'xf-diy-block';
            wrap.setAttribute('data-block-id', block.id);
            wrap.setAttribute('data-kind', block.kind);
            if (block.alias) { wrap.setAttribute('data-alias', block.alias); }

            var label = block.kind === 'component'
                ? (state.components[block.alias] ? state.components[block.alias].label : block.alias)
                : (block.kind === 'row' ? '栅格行' : '栅格列');

            wrap.innerHTML =
                '<span class="xf-diy-block-label">' + esc(label) + '</span>'
                + '<div class="xf-diy-block-toolbar">'
                + '<button type="button" data-act="up" title="上移 (↑)"><i class="ti ti-arrow-up"></i></button>'
                + '<button type="button" data-act="down" title="下移 (↓)"><i class="ti ti-arrow-down"></i></button>'
                + '<button type="button" data-act="dup" title="复制 (Ctrl+D)"><i class="ti ti-copy"></i></button>'
                + '<button type="button" data-act="del" title="删除 (Del)"><i class="ti ti-trash"></i></button>'
                + '</div>'
                + '<div class="xf-diy-block-body"></div>';

            var body = wrap.querySelector('.xf-diy-block-body');

            if (block.kind === 'component') {
                body.innerHTML = '<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm"></span></div>';
                loadPreview(block, body);
            } else if (block.kind === 'row') {
                var rowEl = document.createElement('div');
                rowEl.className = 'xf-diy-row';
                var gut = (block.options && block.options.gutters) || 'g-3';
                rowEl.setAttribute('data-gutters', gut);
                rowEl.style.gap = gutterPx(gut) + 'px';
                rowEl.appendChild(makeRowTag(block.children ? block.children.length : 0));
                body.appendChild(rowEl);
                return buildChildren(rowEl, block.children || []).then(function () {
                    // 按列数 N 与间距扣除 gap，避免「百分比宽度之和 + 间距」超过 100% 导致换行
                    relayoutColumnsInRow(rowEl);
                    return wrap;
                });
            } else {
                var colEl = document.createElement('div');
                colEl.className = 'xf-diy-col';
                colEl.style.width = '100%';
                colEl.style.minHeight = '100%';
                // 列宽（含 gap 扣除）由 relayoutColumnsInRow 统一计算并作用在包裹层 wrap 上
                wrap.style.flex = '0 1 auto';
                wrap.style.minWidth = '0';
                colEl.setAttribute('data-w', colWidthLabel(block.options ? block.options.width : null));
                colEl.setAttribute('data-colclass', colClass(block.options ? block.options.width : null));
                colEl.appendChild(makeColTag(colWidthLabel(block.options ? block.options.width : null)));
                body.appendChild(colEl);
                return buildChildren(colEl, block.children || []).then(function () { return wrap; });
            }
            return Promise.resolve(wrap);
        }

        function buildChildren(container, children) {
            var proms = [];
            (children || []).forEach(function (child) {
                var p = buildBlock(child).then(function (el) { container.appendChild(el); });
                proms.push(p);
            });
            if (!children || !children.length) {
                var empty = document.createElement('div');
                empty.className = 'xf-diy-col-empty';
                empty.innerHTML = '<i class="ti ti-plus"></i> 拖入组件，或点击此' + (container.classList.contains('xf-diy-row') ? '行' : '列') + '设置';
                empty.addEventListener('click', function (ev) {
                    ev.stopPropagation();
                    var blk = empty.closest('.xf-diy-block');
                    if (blk) { selectBlock(blk.getAttribute('data-block-id')); }
                });
                container.appendChild(empty);
            }
            return Promise.all(proms);
        }

        function renderStage() {
            stage.innerHTML = '';
            var empty = stage.querySelector('.xf-diy-stage-empty');
            if (empty) { empty.remove(); }
            if (!state.tree.length) {
                var e = document.createElement('div');
                e.className = 'xf-diy-stage-empty text-center';
                e.innerHTML = '<div class="xf-diy-empty-art"><i class="ti ti-layout-dashboard"></i></div>'
                    + '<div class="h5 mb-1">空白画布</div>'
                    + '<p class="text-muted mb-3">从左侧拖入或点击组件 / 模板开始构建页面，全部由你自由组装。</p>'
                    + '<div class="d-flex gap-2 justify-content-center flex-wrap">'
                    + '<button type="button" class="btn btn-soft-primary btn-sm" data-empty="sample"><i class="ti ti-layout-template me-1"></i>载入示例</button>'
                    + '<button type="button" class="btn btn-soft-secondary btn-sm" data-empty="browse"><i class="ti ti-components me-1"></i>浏览组件</button>'
                    + '</div>'
                    + '<div class="xf-diy-shortcuts mt-3">快捷键：<b>Ctrl+Z</b> 撤销 · <b>Ctrl+D</b> 复制 · <b>Del</b> 删除 · <b>↑/↓</b> 移动 · <b>Esc</b> 取消选择</div>';
                stage.appendChild(e);
                var sb = e.querySelector('[data-empty="sample"]');
                if (sb) { sb.addEventListener('click', loadSample); }
                var bb = e.querySelector('[data-empty="browse"]');
                if (bb) { bb.addEventListener('click', function () { if (search) { search.focus(); search.scrollIntoView({ block: 'nearest' }); } }); }
                return Promise.resolve();
            }
            return buildChildren(stage, state.tree).then(function () {
                initSortable();
                hideEmptyIfNeeded();
            });
        }

        function hideEmptyIfNeeded() {
            var e = stage.querySelector('.xf-diy-stage-empty');
            if (e && state.tree.length) { e.remove(); }
        }

        // ---------- 拖拽 / 排序 ----------
        function initSortable() {
            if (!window.Sortable) { return; }
            var containers = [stage];
            stage.querySelectorAll('.xf-diy-row, .xf-diy-col').forEach(function (c) { containers.push(c); });
            containers.forEach(function (c) {
                if (c.__xfDiySort) { return; }
                c.__xfDiySort = true;
                window.Sortable.create(c, {
                    group: 'xf-diy',
                    animation: 150,
                    handle: '.xf-diy-block',
                    ghostClass: 'xf-diy-drop-line',
                    onEnd: function () {
                        stage.querySelectorAll('.xf-diy-col-empty').forEach(function (e) {
                            if (e.parentElement && e.parentElement.querySelector('.xf-diy-block')) { e.remove(); }
                        });
                        rebuildTreeFromDom();
                        pushHistory();
                    }
                });
            });
        }

        function rebuildTreeFromDom() {
            state.tree = readContainer(stage);
        }
        function readContainer(container) {
            var out = [];
            var nodes = container.children;
            for (var i = 0; i < nodes.length; i++) {
                var n = nodes[i];
                if (!n.classList || !n.classList.contains('xf-diy-block')) { continue; }
                var id = n.getAttribute('data-block-id');
                var block = state.byId[id];
                if (!block) { continue; }
                if (block.kind !== 'component') {
                    var inner = n.querySelector('.xf-diy-row, .xf-diy-col');
                    block.children = inner ? readContainer(inner) : [];
                }
                out.push(block);
            }
            return out;
        }

        function reg(block) {
            if (!block.id) { block.id = newId(); }
            state.byId[block.id] = block;
            return block;
        }
        function preReg(list) {
            (list || []).forEach(function (b) {
                reg(b);
                if (b.children) { preReg(b.children); }
            });
        }
        preReg(state.tree);

        function registerTree(node) {
            state.byId[node.id] = node;
            if (node.children) { node.children.forEach(registerTree); }
        }

        function makeBlock(kind, alias, options) {
            var b = { id: newId(), kind: kind, options: options || {} };
            if (alias) { b.alias = alias; }
            if (kind !== 'component') { b.children = []; }
            return reg(b);
        }

        function appendBlock(targetId, block, index) {
            if (targetId && state.byId[targetId] && state.byId[targetId].kind !== 'component') {
                var ch = state.byId[targetId].children;
                if (typeof index === 'number' && index >= 0) { ch.splice(Math.min(index, ch.length), 0, block); }
                else { ch.push(block); }
            } else {
                if (typeof index === 'number' && index >= 0) { state.tree.splice(Math.min(index, state.tree.length), 0, block); }
                else { state.tree.push(block); }
            }
        }

        function handleDrop(targetId, data, index) {
            if (!data) { return; }
            if (data.kind === 'template') {
                var tpl = state.templates[data.index | 0];
                if (tpl && tpl.tree) {
                    tpl.tree.forEach(function (sub, i) {
                        var b = cloneTree(sub);
                        appendBlock(targetId, b, (typeof index === 'number') ? index + i : undefined);
                    });
                }
            } else if (data.kind === 'component') {
                appendBlock(targetId, makeBlock('component', data.alias, defaultsFor(data.alias)), index);
            } else if (data.kind === 'row' || data.kind === 'col') {
                appendBlock(targetId, makeBlock(data.kind, null, data.kind === 'row' ? { gutters: 'g-3' } : { width: { md: 6 } }), index);
            }
            renderStage().then(function () {
                selectFirstInTarget(targetId, index);
                pushHistory();
                var name = data.kind === 'component' ? (state.components[data.alias] ? state.components[data.alias].label : data.alias)
                    : (data.kind === 'row' ? '栅格行' : (data.kind === 'col' ? '栅格列' : ''));
                toast(data.kind === 'template' ? '已添加模板布局' : '已添加' + name);
            });
        }

        // 点击左侧组件 / 布局元素时，按当前选中块决定放置位置（更贴近用户直觉）
        function addBySelection(kind, alias) {
            var block = kind === 'component'
                ? makeBlock('component', alias, defaultsFor(alias))
                : makeBlock(kind, null, kind === 'row' ? { gutters: 'g-3' } : { width: { md: 6 } });
            var label = kind === 'component' ? (state.components[alias] ? state.components[alias].label : alias)
                : (kind === 'row' ? '栅格行' : '栅格列');
            var sel = state.selected ? state.byId[state.selected] : null;
            if (sel && sel.kind !== 'component') {
                appendBlock(sel.id, block);           // 放入选中的行/列
            } else if (sel && sel.kind === 'component') {
                insertAfter(sel.id, block);            // 作为选中组件的同级插入其后
            } else {
                appendBlock(null, block);              // 舞台根
            }
            renderStage().then(function () { selectBlock(block.id); pushHistory(); toast('已添加' + label); });
        }

        function selectFirstInTarget(targetId, index) {
            return function () {
                var list = targetId && state.byId[targetId] ? state.byId[targetId].children : state.tree;
                if (!list || !list.length) { return; }
                var pos = (typeof index === 'number' && index >= 0 && index < list.length) ? index : list.length - 1;
                selectBlock(list[pos].id);
            };
        }

        // ---------- 行 / 列结构操作 ----------
        function makeCol(width) { var b = makeBlock('col', null, width ? { width: width } : {}); registerTree(b); return b; }
        function makeRow(gutters) { var b = makeBlock('row', null, { gutters: gutters || 'g-3' }); b.children = []; registerTree(b); return b; }

        function findParent(list, id, parent) {
            for (var i = 0; i < list.length; i++) {
                if (list[i].id === id) { return parent || null; }
                if (list[i].children) {
                    var r = findParent(list[i].children, id, list[i]);
                    if (r !== undefined && r !== null) { return r; }
                    if (list[i].children.some(function (c) { return c.id === id; })) { return list[i]; }
                }
            }
            return null;
        }

        function replaceBlock(id, newBlock) {
            function walk(list) {
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id === id) { list[i] = newBlock; return true; }
                    if (list[i].children && walk(list[i].children)) { return true; }
                }
                return false;
            }
            walk(state.tree);
        }

        function equalWidths(n) {
            var base = Math.floor(12 / n), arr = [], rem = 12 - base * n, i;
            for (i = 0; i < n; i++) { arr.push(base + (i < rem ? 1 : 0)); }
            return arr;
        }

        // 把当前列「拆分」为行 + N 个等宽列（保留原内容到首个新列）
        function splitColumn(id, n) {
            var col = state.byId[id];
            if (!col || col.kind !== 'col') { return; }
            var row = makeRow('g-3');
            var first = makeCol({});
            first.children = (col.children || []).map(cloneNoReg);
            row.children.push(first);
            var ws = equalWidths(n);
            first.options.width = { md: ws[0] };
            for (var i = 1; i < n; i++) {
                var c = makeCol({});
                c.options.width = { md: ws[i] };
                row.children.push(c);
            }
            registerTree(row);
            replaceBlock(id, row);
            renderStage().then(function () { selectBlock(row.id); pushHistory(); toast('已拆分为 ' + n + ' 列'); });
        }

        // 在当前列右侧追加一个同级列
        function addSiblingCol(id) {
            var parent = findParent(state.tree, id);
            if (!parent) { return; }
            var col = makeCol({ md: Math.max(1, Math.floor(12 / (parent.children.length + 1))) });
            for (var i = 0; i < parent.children.length; i++) {
                if (parent.children[i].id === id) { parent.children.splice(i + 1, 0, col); break; }
            }
            renderStage().then(function () { selectBlock(col.id); pushHistory(); toast('已添加同级列'); });
        }

        // 向行内追加一个空列
        function addColToRow(id) {
            var row = state.byId[id];
            if (!row || row.kind !== 'row') { return; }
            var col = makeCol({ md: Math.max(1, Math.floor(12 / (row.children.length + 1))) });
            row.children.push(col);
            renderStage().then(function () { selectBlock(col.id); pushHistory(); toast('已向行添加一列'); });
        }

        // 把行内各列等宽均分
        function equalizeRow(id) {
            var row = state.byId[id];
            if (!row || row.kind !== 'row' || !row.children.length) { return; }
            var ws = equalWidths(row.children.length);
            row.children.forEach(function (c, i) {
                c.options.width = { md: ws[i] };
            });
            renderStage().then(function () { selectBlock(row.id); pushHistory(); toast('列宽已均分'); });
        }

        // 一键应用多列等宽布局
        function applyRowLayout(id, parts) {
            var row = state.byId[id];
            if (!row || row.kind !== 'row') { return; }
            row.children = parts.map(function (w) { return makeCol({ md: w }); });
            renderStage().then(function () { selectBlock(row.id); pushHistory(); toast('已应用 ' + parts.length + ' 列布局'); });
        }

        // 克隆（注册新 id）
        function cloneTree(node) {
            var b = makeBlock(node.kind, node.alias || null, JSON.parse(JSON.stringify(node.options || {})));
            if (node.children) { b.children = node.children.map(function (c) { return cloneTree(c); }); }
            return b;
        }
        // 克隆（不注册，用于剪贴板）
        function cloneNoReg(node) {
            var b = { id: newId(), kind: node.kind, options: JSON.parse(JSON.stringify(node.options || {})) };
            if (node.alias) { b.alias = node.alias; }
            if (node.kind !== 'component') { b.children = (node.children || []).map(cloneNoReg); }
            return b;
        }

        function defaultsFor(alias) {
            var c = state.components[alias];
            if (!c) { return {}; }
            // 优先使用 DIY 专用「有内容」默认配置，确保拖入即可见
            var d = (c.diy_defaults && Object.keys(c.diy_defaults).length) ? c.diy_defaults
                : (c.defaults && Object.keys(c.defaults).length ? c.defaults : {});
            return JSON.parse(JSON.stringify(d));
        }

        // ---------- 选择 / 属性面板 ----------
        function emptyPropsHtml() {
            return '<div class="xf-diy-props-empty text-muted text-center py-5">'
                + '<i class="ti ti-click fs-2 d-block mb-2"></i>点击舞台中的组件<br>这里将显示其可编辑属性</div>';
        }

        function selectBlock(id) {
            state.selected = id;
            var prev = stage.querySelectorAll('.xf-diy-block.selected');
            for (var i = 0; i < prev.length; i++) { prev[i].classList.remove('selected'); }
            var el = stage.querySelector('.xf-diy-block[data-block-id="' + cssEsc(id) + '"]');
            if (el) { el.classList.add('selected'); el.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
            renderProps(id);
        }

        // 计算选中块从根到自身的路径（用于面包屑上下文提示）
        function blockPath(id) {
            var path = [];
            function walk(list, trail) {
                for (var i = 0; i < list.length; i++) {
                    var b = list[i];
                    var label = b.kind === 'component'
                        ? (state.components[b.alias] ? state.components[b.alias].label : b.alias)
                        : (b.kind === 'row' ? '栅格行' : '栅格列');
                    var here = trail.concat([label]);
                    if (b.id === id) { path = here; return true; }
                    if (b.children && walk(b.children, here)) { return true; }
                }
                return false;
            }
            walk(state.tree, []);
            return path;
        }

        function renderProps(id) {
            var block = state.byId[id];
            if (!block) { props.innerHTML = emptyPropsHtml(); return; }
            var name = block.kind === 'component'
                ? (state.components[block.alias] ? state.components[block.alias].label : block.alias)
                : (block.kind === 'row' ? '栅格行 (Row)' : '栅格列 (Column)');
            var path = blockPath(id);
            var html = '<div class="xf-diy-prop-meta"><strong>' + esc(name) + '</strong>'
                + '<span class="xf-diy-breadcrumb">' + path.map(function (p) { return esc(p); }).join(' <i class="ti ti-chevron-right"></i> ') + '</span>'
                + '<br><span class="text-muted">类型：' + esc(block.kind) + (block.alias ? ' · ' + esc(block.alias) : '') + '</span></div>';

            if (block.kind === 'component') {
                var comp = state.components[block.alias] || {};
                var pc = comp.schema ? comp.schema.length : 0;
                html += '<div class="xf-diy-comp-meta">'
                    + '<span class="xf-diy-chip"><i class="ti ti-tag"></i> ' + esc(comp.category || '组件') + '</span>'
                    + '<span class="xf-diy-chip"><i class="ti ti-list"></i> ' + pc + ' 项可配置参数</span>'
                    + '<span class="xf-diy-chip"><i class="ti ti-code"></i> ' + esc(block.alias) + '</span></div>';
                html += '<div class="xf-diy-prop-group-title"><i class="ti ti-sliders"></i> 属性 / 配置</div>';
                html += renderFields(block);
                html += '<div class="xf-diy-prop-group-title"><i class="ti ti-code"></i> 原始 JSON（高级 · 可配置任意参数）</div>';
                html += '<div class="xf-diy-field xf-diy-json-editor"><textarea class="form-control" data-json="1">'
                    + esc(JSON.stringify(block.options, null, 2)) + '</textarea>'
                    + '<div class="xf-diy-field-hint">直接编辑全部配置（含未列出的参数），应用后实时刷新预览。</div></div>';
                html += '<div class="d-flex gap-2 mt-2">'
                    + '<button class="btn btn-sm btn-soft-primary" data-reset-example="1" title="用示例默认值覆盖当前配置"><i class="ti ti-refresh me-1"></i>重置为示例</button>'
                    + '<button class="btn btn-sm btn-soft-secondary" data-prop="clear" title="清空内容"><i class="ti ti-eraser me-1"></i>清空内容</button></div>';
            } else if (block.kind === 'row') {
                html += '<div class="xf-diy-prop-group-title"><i class="ti ti-layout-grid"></i> 行设置</div>';
                html += '<div class="xf-diy-field"><label>栅格间距</label>'
                    + selectHtml(['g-2', 'g-3', 'g-4', 'g-5', 'g-0'], block.options.gutters || 'g-3')
                    + '</div>';
                html += '<div class="xf-diy-field"><label>附加 class</label>'
                    + '<input class="form-control" data-rowattr="class" value="' + esc(block.options.class || '') + '"></div>';
                html += '<div class="xf-diy-field"><label>操作</label><div class="d-flex gap-2">'
                    + '<button class="btn btn-sm btn-soft-secondary w-100" data-rowop="addcol">+ 添加列</button>'
                    + '<button class="btn btn-sm btn-soft-secondary w-100" data-rowop="equalize">列宽均分</button></div>'
                    + '<div class="xf-diy-field-hint">行是横向容器，可继续向其中拖入列或组件。</div></div>';
                var rowLayouts = [{ t: '1/2 + 1/2', p: [6, 6] }, { t: '1/3 × 3', p: [4, 4, 4] }, { t: '1/4 × 4', p: [3, 3, 3, 3] }, { t: '1/3 + 2/3', p: [4, 8] }, { t: '1/2 + 1/4 + 1/4', p: [6, 3, 3] }];
                html += '<div class="xf-diy-field"><label>快速布局</label><div class="d-flex gap-1 flex-wrap xf-diy-chips">';
                rowLayouts.forEach(function (l) {
                    html += '<button type="button" class="xf-diy-chip" data-rowlayout="' + l.p.join(',') + '">' + l.t + '</button>';
                });
                html += '</div><div class="xf-diy-field-hint">一键替换为等宽多列布局。</div></div>';
            } else {
                html += '<div class="xf-diy-prop-group-title"><i class="ti ti-layout-columns"></i> 列设置</div>';
                html += '<div class="xf-diy-field"><label>列宽（各断点，1~12，留空=12）</label>'
                    + '<div class="row g-2">';
                ['sm', 'md', 'lg', 'xl', 'xxl'].forEach(function (bp) {
                    var v = block.options.width && typeof block.options.width === 'object' ? (block.options.width[bp] || '') : '';
                    html += '<div class="col"><input class="form-control" type="number" min="1" max="12" data-colw="' + bp + '" value="' + esc(v) + '" placeholder="' + bp + '"></div>';
                });
                html += '</div><div class="xf-diy-field-hint">默认 col-12 占满整行，设置断点后响应式收窄。</div></div>';
                var colPresets = [{ t: '1/4', v: 3 }, { t: '1/3', v: 4 }, { t: '1/2', v: 6 }, { t: '2/3', v: 8 }, { t: '3/4', v: 9 }, { t: '整宽', v: 12 }];
                var curMd = (block.options.width && block.options.width.md) || 0;
                html += '<div class="xf-diy-field"><label>常用宽度</label><div class="d-flex gap-1 flex-wrap xf-diy-chips">';
                colPresets.forEach(function (p) {
                    html += '<button type="button" class="xf-diy-chip' + (curMd == p.v ? ' active' : '') + '" data-colpreset="' + p.v + '">' + p.t + '</button>';
                });
                html += '</div><div class="xf-diy-field-hint">点击快速设置中等断点（md）宽度。</div></div>';
                html += '<div class="xf-diy-field"><label>操作</label><div class="d-flex gap-2 flex-wrap">'
                    + '<button class="btn btn-sm btn-soft-secondary" data-colop="addcol">+ 同级列</button>'
                    + '<button class="btn btn-sm btn-soft-secondary" data-colop="split2">拆 2 列</button>'
                    + '<button class="btn btn-sm btn-soft-secondary" data-colop="split3">拆 3 列</button>'
                    + '<button class="btn btn-sm btn-soft-secondary" data-colop="split4">拆 4 列</button>'
                    + '</div><div class="xf-diy-field-hint">「拆分」会把当前列替换为行+若干等宽列；「同级列」在其右侧追加一列。</div></div>';
            }

            html += '<div class="d-flex gap-2 mt-3">'
                + '<button class="btn btn-sm btn-soft-secondary" data-prop="up" title="上移 (↑)"><i class="ti ti-arrow-up me-1"></i>上移</button>'
                + '<button class="btn btn-sm btn-soft-secondary" data-prop="down" title="下移 (↓)"><i class="ti ti-arrow-down me-1"></i>下移</button>'
                + '<button class="btn btn-sm btn-soft-danger" data-prop="del" title="删除 (Del)"><i class="ti ti-trash me-1"></i>删除</button>'
                + '<button class="btn btn-sm btn-soft-secondary" data-prop="dup" title="复制 (Ctrl+D)"><i class="ti ti-copy me-1"></i>复制</button>'
                + '</div>';

            props.innerHTML = html;
            bindProps(block);
        }

        // 根据字段名推断更友好的输入控件
        function specialType(key) {
            var k = String(key).toLowerCase();
            if (/^(variant|theme)$/.test(k)) { return 'variant'; }
            if (k === 'size') { return 'size'; }
            if (k === 'icon' || /icon$/.test(k)) { return 'icon'; }
            if (/(image|img|avatar|cover|logo|background|bg|thumbnail|picture|photo|src)$/.test(k)) { return 'image'; }
            if (/(url|href|link)$/.test(k) || k === 'link') { return 'url'; }
            if (/(color|colour)$/.test(k)) { return 'color'; }
            return null;
        }

        function getDefaultValue(alias, name) {
            var c = state.components[alias];
            if (c && c.defaults && c.defaults[name] !== undefined) { return c.defaults[name]; }
            return undefined;
        }

        // 由 schema 渲染组件全部可编辑字段（含组件未声明默认值的参数），并按分组呈现
        function renderFields(block) {
            var alias = block.alias;
            var comp = state.components[alias];
            var schema = (comp && comp.schema) || [];
            var html = '';
            if (schema.length) {
                var order = ['基础', '样式', '布局', '内容', '数据', '行为', '其它'];
                var buckets = {};
                schema.forEach(function (f) {
                    var g = f.group || groupOfType(f.type);
                    (buckets[g] = buckets[g] || []).push(f);
                });
                order.forEach(function (g) {
                    if (!buckets[g]) { return; }
                    html += '<div class="xf-diy-fieldset">';
                    if (g !== '基础' || Object.keys(buckets).length > 1) {
                        html += '<div class="xf-diy-prop-group-title xf-diy-sub">' + esc(g) + '</div>';
                    }
                    buckets[g].forEach(function (f) {
                        var val = block.options.hasOwnProperty(f.name) ? block.options[f.name] : getDefaultValue(alias, f.name);
                        html += fieldControl(f, val);
                    });
                    html += '</div>';
                });
            } else {
                var opts = block.options || {};
                Object.keys(opts).forEach(function (k) {
                    html += fieldControl({ name: k, type: inferType(k, opts[k]), label: k }, opts[k]);
                });
                if (!html) { html = '<div class="text-muted small py-2">该组件暂无可编辑参数；可在下方「原始 JSON」中自由设置任意参数。</div>'; }
            }
            return html;
        }

        // 控件类型 → 默认分组
        function groupOfType(t) {
            if (t === 'select' || t === 'icon' || t === 'image' || t === 'color') { return '样式'; }
            if (t === 'json') { return '数据'; }
            if (t === 'bool' || t === 'number') { return '基础'; }
            return '内容';
        }

        function inferType(key, value) {
            var k = String(key).toLowerCase();
            if (typeof value === 'boolean') { return 'bool'; }
            if (typeof value === 'number') { return 'number'; }
            if (value !== null && typeof value === 'object') { return 'json'; }
            if (/^(variant|theme)$/.test(k)) { return 'select-variant'; }
            if (k === 'size') { return 'select-size'; }
            if (k === 'icon' || /icon$/.test(k)) { return 'icon'; }
            if (/(image|img|avatar|cover|logo|background|bg|thumbnail|picture|photo|src)$/.test(k)) { return 'image'; }
            if (/(url|href|link)$/.test(k) || k === 'link') { return 'url'; }
            if (/(color|colour)$/.test(k)) { return 'color'; }
            if (typeof value === 'string' && (value.length > 50 || value.indexOf('\n') !== -1)) { return 'textarea'; }
            return 'text';
        }

        // 单字段控件（def: {name,type,label,options?,placeholder?,hint?}）
        function fieldControl(def, value) {
            var name = def.name, type = def.type, label = def.label || def.name;
            var hint = def.hint ? '<div class="xf-diy-field-hint">' + esc(def.hint) + '</div>' : '';
            var ph = def.placeholder ? ' placeholder="' + esc(def.placeholder) + '"' : '';
            var L = '<label>' + esc(label) + '</label>';
            var sv = value == null ? '' : String(value);

            // 变体 / 主题：彩色色块选择器（替代下拉，更直观）
            if ((type === 'select' || type === 'select-variant') && /^(variant|theme)$/.test(name)) {
                return variantSwatches(name, label, sv, hint);
            }

            if (type === 'bool') {
                return '<div class="xf-diy-field"><div class="form-check form-switch">'
                    + '<input class="form-check-input" type="checkbox" data-field="' + esc(name) + '" data-type="bool"'
                    + (value ? ' checked' : '') + '><label class="form-check-label">' + esc(label) + '</label></div></div>';
            }
            if (type === 'number') {
                return '<div class="xf-diy-field">' + L + '<input class="form-control" type="number" data-field="' + esc(name)
                    + '" data-type="number" value="' + esc(sv) + '"></div>';
            }
            if (type === 'select' || type === 'select-variant' || type === 'select-size') {
                var opts = def.options ? def.options.slice() : (type === 'select-variant'
                    ? ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'none']
                    : ['xs', 'sm', 'md', 'lg', 'xl', 'xxl']);
                if (opts.indexOf(sv) === -1 && sv !== '') { opts = [sv].concat(opts); }
                return '<div class="xf-diy-field">' + L + selectOptions(opts, sv, name, 'select') + '</div>';
            }
            if (type === 'json') {
                var jv = (value && typeof value === 'object') ? JSON.stringify(value, null, 2) : sv;
                return '<div class="xf-diy-field">' + L + '<textarea class="form-control xf-diy-ta-json" data-field="' + esc(name)
                    + '" data-type="json">' + esc(jv) + '</textarea>' + hint + '</div>';
            }
            if (type === 'color') {
                return '<div class="xf-diy-field">' + L + '<div class="xf-diy-field-row">'
                    + '<input type="color" class="form-control form-control-color" data-field="' + esc(name) + '" data-type="color" value="' + esc(toHex(sv)) + '">'
                    + '<input type="text" class="form-control" data-field="' + esc(name) + '" data-type="color" value="' + esc(sv) + '"></div>' + hint + '</div>';
            }
            if (type === 'icon') {
                return '<div class="xf-diy-field">' + L + '<div class="xf-diy-field-row">'
                    + '<span class="xf-diy-icon-prev ' + (sv ? 'ti ' + esc(sv) : 'ti ti-icons-off') + '" data-prev></span>'
                    + '<input type="text" class="form-control" data-field="' + esc(name) + '" data-type="icon" value="' + esc(sv) + '" placeholder="ti ti-...">'
                    + '<button type="button" class="btn btn-sm btn-soft-secondary xf-diy-icon-pick" data-iconpick="' + esc(name) + '" title="选择图标"><i class="ti ti-icons"></i></button>'
                    + '</div>' + hint + '</div>';
            }
            if (type === 'image') {
                return '<div class="xf-diy-field">' + L + '<div class="xf-diy-field-row">'
                    + '<img class="xf-diy-img-prev" data-prev src="' + esc(sv) + '" onerror="this.style.visibility=\'hidden\'" onload="this.style.visibility=\'visible\'">'
                    + '<input type="text" class="form-control" data-field="' + esc(name) + '" data-type="image" value="' + esc(sv) + '" placeholder="图片 URL"></div>' + hint + '</div>';
            }
            if (type === 'url') {
                return '<div class="xf-diy-field">' + L + '<input type="url" class="form-control" data-field="' + esc(name)
                    + '" data-type="url" value="' + esc(sv) + '"></div>';
            }
            if (type === 'textarea') {
                return '<div class="xf-diy-field">' + L + '<textarea class="form-control" data-field="' + esc(name)
                    + '" data-type="string">' + esc(sv) + '</textarea></div>';
            }
            return '<div class="xf-diy-field">' + L + '<input class="form-control" type="text" data-field="' + esc(name)
                + '" data-type="string" value="' + esc(sv) + '"' + ph + '></div>';
        }

        function selectOptions(options, current, key, type) {
            var h = '<select class="form-select" data-field="' + esc(key) + '" data-type="' + esc(type) + '">';
            options.forEach(function (o) { h += '<option value="' + esc(o) + '"' + (o === current ? ' selected' : '') + '>' + esc(o) + '</option>'; });
            return h + '</select>';
        }

        function toHex(v) {
            if (typeof v === 'string' && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(v.trim())) { return v.trim(); }
            return '#3b82f6';
        }

        // 变体色板
        var VARIANT_COLORS = { primary: '#3b82f6', secondary: '#64748b', success: '#22c55e', danger: '#ef4444', warning: '#f59e0b', info: '#0ea5e9', light: '#e2e8f0', dark: '#1e293b', none: 'transparent' };
        function bsColor(v) { return VARIANT_COLORS[v] || '#3b82f6'; }
        function variantSwatches(name, label, sv, hint) {
            var list = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'none'];
            var html = '<div class="xf-diy-field"><label>' + esc(label) + '</label><div class="xf-diy-swatches" data-swatch="' + esc(name) + '">';
            list.forEach(function (v) {
                html += '<button type="button" class="xf-diy-swatch' + (sv === v ? ' active' : '') + (v === 'none' ? ' is-none' : '')
                    + '" data-v="' + esc(v) + '" title="' + esc(v) + '" style="--sw:' + bsColor(v) + '"></button>';
            });
            html += '</div>' + (hint || '') + '</div>';
            return html;
        }

        // 图标选择器：可搜索的 Tabler 图标库
        var ICONS = ['home', 'home-2', 'user', 'user-plus', 'user-minus', 'users', 'user-check', 'user-x', 'user-circle', 'user-heart',
            'settings', 'adjustments', 'tool', 'tools', 'bolt', 'bulb', 'rocket', 'flask', 'star', 'star-filled',
            'heart', 'heart-handshake', 'thumb-up', 'thumb-down', 'mood-happy', 'mood-sad', 'mood-smile',
            'check', 'checklist', 'circle-check', 'x', 'square-x', 'plus', 'minus', 'trash', 'edit', 'pencil',
            'copy', 'clipboard', 'clipboard-check', 'clipboard-copy', 'eye', 'eye-off', 'lock', 'lock-open', 'unlock', 'key',
            'shield', 'shield-check', 'shield-half', 'award', 'crown', 'medal', 'gift', ' bookmark', 'flag', 'tag',
            'search', 'filter', 'sort', 'sort-ascending', 'sort-descending', 'list', 'list-check', 'grid-dots', 'layout',
            'layout-grid', 'layout-columns', 'layout-rows', 'layout-dashboard', 'layout-sidebar', 'layout-cards',
            'device-desktop', 'device-desktop-analytics', 'device-tablet', 'device-mobile', 'device-tv', 'device-laptop',
            'bell', 'bell-ringing', 'mail', 'mail-opened', 'send', 'message', 'messages', 'message-circle', 'message-2',
            'phone', 'phone-call', 'calendar', 'calendar-event', 'calendar-time', 'clock', 'clock-hour-3', 'alarm',
            'image', 'photo', 'photo-plus', 'images', 'file', 'file-text', 'file-plus', 'file-check', 'folder', 'folder-plus',
            'download', 'upload', 'cloud', 'cloud-upload', 'cloud-download', 'wifi', 'wifi-off', 'sun', 'moon', 'moon-stars',
            'printer', 'save', 'refresh', 'refresh-dot', 'repeat', 'share', 'external-link', 'link', 'unlink', 'anchor',
            'code', 'code-dots', 'terminal', 'bug', 'alert', 'alert-triangle', 'alert-circle', 'info-circle', 'help', 'help-circle',
            'package', 'box', 'box-seam', 'truck', 'building', 'building-store', 'map-pin', 'map-2', 'globe', 'world',
            'credit-card', 'shopping-cart', 'shopping-bag', 'currency-yuan', 'currency-dollar', 'coin', 'wallet', 'pig-money',
            'chart-bar', 'chart-pie', 'chart-line', 'chart-area', 'chart-dots', 'chart-arcs', 'database', 'server',
            'logout', 'login', 'user-plus', 'user-minus', 'users-plus', 'accessible', 'access-point',
            'brand-github', 'brand-apple', 'brand-android', 'brand-chrome', 'brand-facebook', 'brand-twitter', 'brand-youtube',
            'point', 'point-filled', 'dots', 'dots-circle-horizontal', 'puzzle', 'components', 'app', 'apps', 'app-window',
            'arrow-right', 'arrow-left', 'arrow-up', 'arrow-down', 'arrow-up-right', 'arrow-big-up', 'chevron-right', 'chevron-down',
            'chevron-left', 'chevron-up', 'chevrons-right', 'chevrons-left', 'corner-up-left', 'corner-up-right',
            'player-play', 'player-pause', 'player-stop', 'player-skip-forward', 'volume', 'microphone', 'camera', 'video',
            'speakerphone', 'notification', 'bell-ringing', 'ticket', 'id', 'id-badge', 'address-book', 'contacts',
            'temperature', 'droplet', 'wind', 'flame', 'leaf', 'plant', 'tree', 'car', 'bike', 'bus',
            'coffee', 'cup', 'glass', 'wine', 'salad', 'meat', 'egg', 'cake', 'cookie', 'brand-css3',
            'bookmark', 'book', 'books', 'notebook', 'note', 'notes', 'writing', 'typography', 'writing-sign',
            'script', 'article', 'news', 'new-section', 'quote', 'blockquote', 'separator', 'paragraph', 'heading',
            'table', 'columns', 'rows', 'grid-pattern', 'layout-board', 'layout-list', 'layout-grid-add',
            'zoom-in', 'zoom-out', 'focus', 'maximize', 'minimize', 'resize', 'move', 'arrows-move', 'drag-drop',
            'checkbox', 'circle-dot', 'toggle-left', 'toggle-right', 'switch', 'adjustments-horizontal', 'sliders', 'slider'];
        function openIconPicker(input, onPick) {
            closeIconPicker();
            var mask = document.createElement('div');
            mask.className = 'xf-diy-icon-mask';
            var grid = '';
            ICONS.forEach(function (n) {
                var nm = String(n).trim();
                if (!nm) { return; }
                grid += '<button type="button" class="xf-diy-icon-opt" data-ic="ti ti-' + esc(nm) + '" title="' + esc(nm) + '"><i class="ti ti-' + esc(nm) + '"></i></button>';
            });
            mask.innerHTML = '<div class="xf-diy-icon-pop">'
                + '<div class="xf-diy-icon-pop-head"><input type="text" class="form-control form-control-sm xf-diy-icon-search" placeholder="搜索图标…">'
                + '<button type="button" class="xf-diy-icon-close" data-icclose="1">&times;</button></div>'
                + '<div class="xf-diy-icon-grid">' + grid + '</div></div>';
            document.body.appendChild(mask);
            var pop = mask.querySelector('.xf-diy-icon-pop');
            if (input) {
                var r = input.getBoundingClientRect();
                var top = Math.min(window.innerHeight - 380, r.bottom + 8);
                var left = Math.min(window.innerWidth - 330, Math.max(8, r.left));
                pop.style.position = 'fixed';
                pop.style.top = Math.max(8, top) + 'px';
                pop.style.left = left + 'px';
            }
            var search = mask.querySelector('.xf-diy-icon-search');
            var gridEl = mask.querySelector('.xf-diy-icon-grid');
            function filter(q) {
                q = (q || '').trim().toLowerCase();
                gridEl.querySelectorAll('.xf-diy-icon-opt').forEach(function (b) {
                    var n = b.getAttribute('data-ic').replace('ti ti-', '');
                    b.style.display = (!q || n.indexOf(q) !== -1) ? '' : 'none';
                });
            }
            search.addEventListener('input', function () { filter(search.value); });
            if (input && input.value) { search.value = input.value.replace(/^ti ti-/, ''); filter(search.value); }
            gridEl.addEventListener('click', function (e) {
                var b = e.target.closest('.xf-diy-icon-opt');
                if (!b) { return; }
                var ic = b.getAttribute('data-ic');
                if (input) { input.value = ic; }
                onPick(ic);
                closeIconPicker();
            });
            mask.addEventListener('click', function (e) {
                if (e.target === mask || e.target.getAttribute('data-icclose')) { closeIconPicker(); }
            });
            mask._xfEsc = function (e) { if (e.key === 'Escape') { closeIconPicker(); } };
            document.addEventListener('keydown', mask._xfEsc);
            setTimeout(function () { search.focus(); }, 0);
        }
        function closeIconPicker() {
            var m = document.querySelector('.xf-diy-icon-mask');
            if (m) {
                if (m._xfEsc) { document.removeEventListener('keydown', m._xfEsc); }
                m.remove();
            }
        }

        function selectHtml(options, current) {
            return selectOptions(options, current, 'gutters', 'rowattr');
        }

        // 用组件示例默认值覆盖当前配置
        function resetToExample(id) {
            var b = byId(id);
            if (!b) { return; }
            var comp = state.components[b.alias];
            var dd = comp && comp.diy_defaults;
            if (!dd) { toast('该组件无示例默认值，可手动编辑或清空。', 'warning'); return; }
            b.options = JSON.parse(JSON.stringify(dd));
            renderStage().then(function () {
                selectBlock(b.id);
                pushHistory();
            });
            toast('已重置为示例默认值');
        }

        function bindProps(block) {
            props.querySelectorAll('[data-field]').forEach(function (inp) {
                var key = inp.getAttribute('data-field');
                var t = inp.getAttribute('data-type');
                var evt = (inp.tagName === 'SELECT' || inp.type === 'checkbox' || t === 'json') ? 'change' : 'input';
                inp.addEventListener(evt, function () {
                    var val;
                    try {
                        if (t === 'bool') { val = inp.checked; }
                        else if (t === 'number') { val = inp.value === '' ? null : Number(inp.value); }
                        else if (t === 'json') { val = JSON.parse(inp.value); }
                        else { val = inp.value; }
                    } catch (e) {
                        if (state.XF && state.XF.toast) { state.XF.toast({ body: 'JSON 格式错误：' + e.message, variant: 'danger' }); }
                        return;
                    }
                    block.options[key] = val;
                    // 颜色双向同步
                    if (t === 'color') {
                        props.querySelectorAll('[data-field="' + cssEsc(key) + '"][data-type="color"]').forEach(function (o) {
                            if (o !== inp) { o.value = val; }
                        });
                    }
                    // 图标 / 图片实时预览
                    var prev = inp.parentNode ? inp.parentNode.querySelector('[data-prev]') : null;
                    if (prev) {
                        if (t === 'icon') { prev.className = 'xf-diy-icon-prev ' + (val ? 'ti ' + val : 'ti ti-icons-off'); }
                        else if (t === 'image') { prev.src = val || ''; prev.style.visibility = val ? 'visible' : 'hidden'; }
                    }
                    if (t === 'json') { syncJsonEditor(block); }
                    updateComponentPreview(block);
                    scheduleHistory();
                });
            });
            props.querySelectorAll('[data-rowattr]').forEach(function (inp) {
                inp.addEventListener('change', function () {
                    block.options[inp.getAttribute('data-rowattr')] = inp.value;
                    applyContainerClass(block);
                    scheduleHistory();
                });
            });
            props.querySelectorAll('[data-colw]').forEach(function (inp) {
                inp.addEventListener('input', function () {
                    block.options.width = block.options.width && typeof block.options.width === 'object' ? block.options.width : {};
                    var n = inp.value === '' ? '' : Math.max(1, Math.min(12, parseInt(inp.value, 10) || 0));
                    if (n === '' || isNaN(n)) { delete block.options.width[inp.getAttribute('data-colw')]; }
                    else { block.options.width[inp.getAttribute('data-colw')] = n; }
                    applyContainerClass(block);
                    scheduleHistory();
                });
            });
            var jsonTa = props.querySelector('[data-json]');
            if (jsonTa) {
                jsonTa.addEventListener('change', function () {
                    try {
                        block.options = JSON.parse(jsonTa.value);
                        syncFieldsFromOptions(block);
                        updateComponentPreview(block);
                        pushHistory();
                    } catch (e) {
                        if (state.XF && state.XF.toast) { state.XF.toast({ body: 'JSON 格式错误：' + e.message, variant: 'danger' }); }
                    }
                });
            }
            var del = props.querySelector('[data-prop="del"]');
            var dup = props.querySelector('[data-prop="dup"]');
            var up = props.querySelector('[data-prop="up"]');
            var down = props.querySelector('[data-prop="down"]');
            if (del) { del.addEventListener('click', function () { removeBlock(block.id); }); }
            if (dup) { dup.addEventListener('click', function () { duplicateBlock(block.id); }); }
            if (up) { up.addEventListener('click', function () { moveBlock(block.id, 'up'); }); }
            if (down) { down.addEventListener('click', function () { moveBlock(block.id, 'down'); }); }
            var clearBtn = props.querySelector('[data-prop="clear"]');
            if (clearBtn) { clearBtn.addEventListener('click', function () { block.options = {}; renderStage().then(function () { selectBlock(block.id); pushHistory(); }); toast('已清空内容'); }); }
            var resetBtn = props.querySelector('[data-reset-example]');
            if (resetBtn) { resetBtn.addEventListener('click', function () { resetToExample(block.id); }); }

            // 行/列结构操作
            props.querySelectorAll('[data-rowop]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var op = b.getAttribute('data-rowop');
                    if (op === 'addcol') { addColToRow(block.id); }
                    else if (op === 'equalize') { equalizeRow(block.id); }
                });
            });
            props.querySelectorAll('[data-colop]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var op = b.getAttribute('data-colop');
                    if (op === 'addcol') { addSiblingCol(block.id); }
                    else if (op === 'split2') { splitColumn(block.id, 2); }
                    else if (op === 'split3') { splitColumn(block.id, 3); }
                    else if (op === 'split4') { splitColumn(block.id, 4); }
                });
            });
            props.querySelectorAll('[data-colpreset]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var v = parseInt(b.getAttribute('data-colpreset'), 10);
                    block.options.width = block.options.width || {};
                    block.options.width.md = v;
                    renderStage().then(function () { selectBlock(block.id); pushHistory(); });
                });
            });
            props.querySelectorAll('[data-rowlayout]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var parts = b.getAttribute('data-rowlayout').split(',').map(function (x) { return parseInt(x, 10); });
                    applyRowLayout(block.id, parts);
                });
            });

            // 变体色板选择
            props.querySelectorAll('[data-swatch]').forEach(function (sw) {
                sw.addEventListener('click', function (e) {
                    var b = e.target.closest('.xf-diy-swatch');
                    if (!b) { return; }
                    var key = sw.getAttribute('data-swatch');
                    var v = b.getAttribute('data-v');
                    block.options[key] = v;
                    sw.querySelectorAll('.xf-diy-swatch').forEach(function (x) { x.classList.toggle('active', x === b); });
                    updateComponentPreview(block);
                    scheduleHistory();
                });
            });
            // 图标选择器
            props.querySelectorAll('[data-iconpick]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var name = btn.getAttribute('data-iconpick');
                    var inp = props.querySelector('[data-field="' + cssEsc(name) + '"][data-type="icon"]');
                    openIconPicker(inp, function (val) {
                        block.options[name] = val;
                        var prev = inp.parentNode ? inp.parentNode.querySelector('[data-prev]') : null;
                        if (prev) { prev.className = 'xf-diy-icon-prev ' + (val ? 'ti ' + val : 'ti ti-icons-off'); }
                        updateComponentPreview(block);
                        scheduleHistory();
                    });
                });
            });
        }

        function syncJsonEditor(block) {
            var ta = props.querySelector('[data-json]');
            if (ta && document.activeElement !== ta) { ta.value = JSON.stringify(block.options, null, 2); }
        }
        function syncFieldsFromOptions(block) { renderProps(block.id); }

        function applyContainerClass(block) {
            var el = stage.querySelector('.xf-diy-block[data-block-id="' + cssEsc(block.id) + '"]');
            if (!el) { return; }
            if (block.kind === 'row') {
                var row = el.querySelector('.xf-diy-row');
                row.className = 'xf-diy-row ' + (block.options.gutters || 'g-3') + ' ' + (block.options.class || '');
                row.style.gap = gutterPx(block.options.gutters || 'g-3') + 'px';
                var rtag = row.querySelector('.xf-diy-row-tag');
                if (rtag) { rtag.textContent = (block.children ? block.children.length : 0) + ' 列'; }
            } else if (block.kind === 'col') {
                var col = el.querySelector('.xf-diy-col');
                // 列宽（含 gap 扣除）由 relayoutColumnsInRow 作用在包裹层 el 上，内层 .xf-diy-col 充满包裹层
                el.style.flex = '0 1 auto';
                el.style.minWidth = '0';
                col.className = 'xf-diy-col ' + (block.options.class || '');
                col.style.width = '100%';
                col.style.minHeight = '100%';
                col.setAttribute('data-w', colWidthLabel(block.options.width));
                col.setAttribute('data-colclass', colClass(block.options.width));
                var ctag = col.querySelector('.xf-diy-col-tag');
                if (ctag) { ctag.textContent = colWidthLabel(block.options.width); }
                relayoutColumnsInRow(el.closest('.xf-diy-row'));
            }
        }

        function relayoutColumnsInRow(rowEl) {
            if (!rowEl || !rowEl.classList || !rowEl.classList.contains('xf-diy-row')) { return; }
            var gut = rowEl.getAttribute('data-gutters') || 'g-3';
            var gap = gutterPx(gut);
            var wrappers = rowEl.querySelectorAll(':scope > .xf-diy-block[data-kind="col"]');
            var N = wrappers.length;
            if (!N) { return; }
            wrappers.forEach(function (wEl) {
                var id = wEl.getAttribute('data-block-id');
                var blk = state.byId[id];
                var w = colMd(blk ? blk.options.width : null);
                var calc = 'calc((100% - ' + (gap * (N - 1)) + 'px) * ' + (w / 12) + ')';
                wEl.style.width = calc;
                wEl.style.maxWidth = calc;
            });
        }
        function colClass(width) {
            if (!width) { return 'col-12'; }
            if (typeof width === 'object') {
                var cls = 'col-12';
                ['sm', 'md', 'lg', 'xl', 'xxl'].forEach(function (bp) {
                    if (width[bp]) { cls += ' col-' + bp + '-' + width[bp]; }
                });
                return cls;
            }
            var n = parseInt(width, 10);
            return (n < 1 || n > 12) ? 'col-12' : 'col-12 col-md-' + n + ' col-xl-' + n;
        }
        function colWidthLabel(width) {
            if (!width || typeof width !== 'object') { return '12/12'; }
            var n = width.md || width.lg || width.xl || width.xxl || width.sm || 12;
            return n + '/12';
        }
        // 取栅格列的 md 栅格数（1-12），缺省 12（整宽）
        function colMd(width) {
            if (!width || typeof width !== 'object') { return 12; }
            return width.md || width.lg || width.xl || width.xxl || width.sm || 12;
        }
        // 栅格间距 → 像素间距
        function gutterPx(g) {
            var map = { 'g-0': 0, 'g-1': 4, 'g-2': 8, 'g-3': 16, 'g-4': 24, 'g-5': 48 };
            return (g && map[g] !== undefined) ? map[g] : 16;
        }
        // 列宽标签（舞台中显示，便于辨识栅格宽度）
        function makeColTag(label) {
            var t = document.createElement('span');
            t.className = 'xf-diy-col-tag';
            t.textContent = label;
            return t;
        }
        // 列数标签（行顶部显示「N 列」）
        function makeRowTag(n) {
            var t = document.createElement('span');
            t.className = 'xf-diy-row-tag';
            t.textContent = n + ' 列';
            return t;
        }

        var previewTimers = {};
        function updateComponentPreview(block) {
            if (previewTimers[block.id]) { clearTimeout(previewTimers[block.id]); }
            previewTimers[block.id] = setTimeout(function () {
                var el = stage.querySelector('.xf-diy-block[data-block-id="' + cssEsc(block.id) + '"] .xf-diy-block-body');
                if (el) { loadPreview(block, el); }
            }, 280);
        }

        // ---------- 历史（撤销/重做）----------
        function snapshot() { return JSON.stringify(state.tree); }
        function seedHistory() { state.history = [snapshot()]; state.histIndex = 0; updateHistoryButtons(); }
        function pushHistory() {
            var snap = snapshot();
            if (state.history[state.histIndex] === snap) { return; }
            state.history = state.history.slice(0, state.histIndex + 1);
            state.history.push(snap);
            if (state.history.length > 120) { state.history.shift(); }
            state.histIndex = state.history.length - 1;
            updateHistoryButtons();
            saveLocal();
        }
        var scheduleHistory = debounce(pushHistory, 600);
        function undo() {
            if (state.histIndex <= 0) { return; }
            state.histIndex--;
            restore(state.history[state.histIndex]);
            toast('已撤销');
        }
        function redo() {
            if (state.histIndex >= state.history.length - 1) { return; }
            state.histIndex++;
            restore(state.history[state.histIndex]);
            toast('已重做');
        }
        function restore(snap) {
            try { state.tree = JSON.parse(snap); } catch (e) { return; }
            state.byId = {}; preReg(state.tree);
            state.selected = null;
            renderStage().then(function () { props.innerHTML = emptyPropsHtml(); updateHistoryButtons(); });
        }
        function updateHistoryButtons() {
            var u = root.querySelector('.xf-diy-btn-undo');
            var r = root.querySelector('.xf-diy-btn-redo');
            if (u) { u.disabled = state.histIndex <= 0; }
            if (r) { r.disabled = state.histIndex >= state.history.length - 1; }
        }

        // ---------- 块操作 ----------
        function removeBlock(id) {
            delete state.byId[id];
            state.tree = removeFromList(state.tree, id);
            if (state.selected === id) { state.selected = null; }
            renderStage().then(function () {
                props.innerHTML = emptyPropsHtml();
                pushHistory();
                toast('已删除');
            });
        }
        function removeFromList(list, id) {
            return list.filter(function (b) {
                if (b.id === id) { return false; }
                if (b.children) { b.children = removeFromList(b.children, id); }
                return true;
            });
        }
        function duplicateBlock(id) {
            var src = state.byId[id];
            if (!src) { return; }
            var copy = cloneTree(src);
            insertAfter(id, copy);
            renderStage().then(function () { selectBlock(copy.id); pushHistory(); toast('已复制一份'); });
        }
        function insertAfter(id, block) {
            function walk(list) {
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id === id) { list.splice(i + 1, 0, block); return true; }
                    if (list[i].children && walk(list[i].children)) { return true; }
                }
                return false;
            }
            if (!walk(state.tree)) { state.tree.push(block); }
        }

        function copyBlock(id) {
            var src = state.byId[id];
            if (!src) { return; }
            state.clipboard = cloneNoReg(src);
            toast('已复制到剪贴板 (Ctrl+V 粘贴)');
        }
        function pasteBlock() {
            if (!state.clipboard) { toast('剪贴板为空'); return; }
            var copy = cloneNoReg(state.clipboard);
            registerTree(copy);
            appendBlock(state.selected, copy);
            renderStage().then(function () { selectBlock(copy.id); pushHistory(); toast('已粘贴'); });
        }

        // 工具栏按钮（上移/下移/复制/删除）
        stage.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('[data-act]') : null;
            if (!btn) {
                var b = e.target.closest ? e.target.closest('.xf-diy-block') : null;
                if (b) { selectBlock(b.getAttribute('data-block-id')); }
                return;
            }
            var wrap = btn.closest('.xf-diy-block');
            var id = wrap.getAttribute('data-block-id');
            var act = btn.getAttribute('data-act');
            if (act === 'del') { removeBlock(id); }
            else if (act === 'dup') { duplicateBlock(id); }
            else if (act === 'up' || act === 'down') { moveBlock(id, act); }
        });

        function moveBlock(id, dir) {
            function walk(list) {
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id === id) {
                        var j = dir === 'up' ? i - 1 : i + 1;
                        if (j >= 0 && j < list.length) {
                            var t = list[i]; list[i] = list[j]; list[j] = t; return true;
                        }
                        return false;
                    }
                    if (list[i].children && walk(list[i].children)) { return true; }
                }
                return false;
            }
            walk(state.tree);
            renderStage().then(function () { selectBlock(id); pushHistory(); toast(act === 'up' ? '已上移' : '已下移'); });
        }

        // ---------- 拖拽源（左侧组件 / 模板 / 布局元素）----------
        root.addEventListener('dragstart', function (e) {
            var item = e.target.closest ? e.target.closest('.xf-diy-comp-item') : null;
            var tpl = e.target.closest ? e.target.closest('.xf-diy-tpl') : null;
            if (item) {
                var kind = item.getAttribute('data-diy-kind');
                if (kind === 'row' || kind === 'col') {
                    e.dataTransfer.setData('application/x-xf-diy', JSON.stringify({ kind: kind }));
                } else {
                    e.dataTransfer.setData('application/x-xf-diy', JSON.stringify({ kind: 'component', alias: item.getAttribute('data-alias') }));
                }
                e.dataTransfer.effectAllowed = 'copy';
            } else if (tpl) {
                e.dataTransfer.setData('application/x-xf-diy', JSON.stringify({ kind: 'template', index: parseInt(tpl.getAttribute('data-tpl'), 10) }));
                e.dataTransfer.effectAllowed = 'copy';
            }
        });

        root.querySelectorAll('.xf-diy-tpl').forEach(function (t) {
            t.addEventListener('click', function () {
                handleDrop(null, { kind: 'template', index: parseInt(t.getAttribute('data-tpl'), 10) });
            });
        });

        // 放置目标：stage + 各 row/col（按光标位置精确插入）
        function directBlocks(container) {
            return Array.prototype.filter.call(container.children, function (c) {
                return c.classList && c.classList.contains('xf-diy-block');
            });
        }
        function getDropIndex(container, x, y) {
            var blocks = directBlocks(container);
            var horizontal = container.classList.contains('xf-diy-row');
            for (var i = 0; i < blocks.length; i++) {
                var r = blocks[i].getBoundingClientRect();
                var mid = horizontal ? (r.left + r.width / 2) : (r.top + r.height / 2);
                if (horizontal ? x < mid : y < mid) { return i; }
            }
            return blocks.length;
        }
        function showDropIndicator(node, index, horizontal) {
            removeDropIndicator(node);
            var ind = document.createElement('div');
            ind.className = 'xf-diy-drop-indicator' + (horizontal ? ' horizontal' : '');
            var blocks = directBlocks(node);
            if (index >= blocks.length) { node.appendChild(ind); }
            else { node.insertBefore(ind, blocks[index]); }
        }
        function removeDropIndicator(node) {
            var ind = node.querySelector(':scope > .xf-diy-drop-indicator');
            if (ind) { ind.remove(); }
        }
        function bindDropTarget(node, targetId) {
            node.addEventListener('dragover', function (e) {
                if (!hasDiyData(e)) { return; }
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
                // 命中更内层容器时，由内层负责指示，外层不重复画
                var inner = e.target.closest ? e.target.closest('.xf-diy-row, .xf-diy-col') : null;
                if (inner && inner !== node && node.contains(inner)) { return; }
                node.classList.add('drag-over');
                showDropIndicator(node, getDropIndex(node, e.clientX, e.clientY), node.classList.contains('xf-diy-row'));
            });
            node.addEventListener('dragleave', function (e) {
                if (e.relatedTarget && node.contains(e.relatedTarget)) { return; }
                node.classList.remove('drag-over');
                removeDropIndicator(node);
            });
            node.addEventListener('drop', function (e) {
                if (!hasDiyData(e)) { return; }
                e.preventDefault();
                e.stopPropagation(); // 避免冒泡到父容器（行/列/舞台）重复插入
                node.classList.remove('drag-over');
                removeDropIndicator(node);
                var raw = e.dataTransfer.getData('application/x-xf-diy');
                if (!raw) { return; }
                var data; try { data = JSON.parse(raw); } catch (err) { return; }
                var idx = getDropIndex(node, e.clientX, e.clientY);
                handleDrop(targetId, data, idx);
            });
        }
        function hasDiyData(e) {
            try { return e.dataTransfer.types && Array.prototype.indexOf.call(e.dataTransfer.types, 'application/x-xf-diy') !== -1; }
            catch (err) { return false; }
        }
        bindDropTarget(stage, null);
        var origBuildChildren = buildChildren;
        buildChildren = function (container, children) {
            return origBuildChildren(container, children).then(function () {
                if (container !== stage && (container.classList.contains('xf-diy-row') || container.classList.contains('xf-diy-col'))) {
                    bindDropTarget(container, container.closest('.xf-diy-block') ? container.closest('.xf-diy-block').getAttribute('data-block-id') : null);
                }
                return;
            });
        };

        // ---------- 顶部工具栏：设备 / 历史 / 预览 / 导入 / 导出 / 清空 ----------
        // 设备预览：整块舞台作为设备画布（桌面铺满 / 平板·手机按宽度并居中）
        function applyDevice(dev) {
            state.device = dev;
            root.querySelectorAll('.xf-diy-devices button').forEach(function (b) {
                b.classList.toggle('active', b.getAttribute('data-dev') === dev);
            });
            stage.classList.remove('xf-diy-dev-desktop', 'xf-diy-dev-tablet', 'xf-diy-dev-mobile');
            stage.classList.add('xf-diy-dev-' + dev);
            // 所有设备（含桌面）均可自定义画布宽度
            var dwrap = root.querySelector('.xf-diy-dev-width-wrap');
            var dinput = root.querySelector('.xf-diy-dev-width');
            if (dwrap) { dwrap.style.display = 'flex'; }
            if (dinput) {
                var cur = state.devWidth[dev];
                dinput.value = (cur === null || cur === undefined) ? '' : cur;
                applyDevWidth();
            }
        }
        function applyDevWidth() {
            var dinput = root.querySelector('.xf-diy-dev-width');
            var dev = state.device;
            if (!dinput) { return; }
            var raw = parseInt(dinput.value, 10);
            if (!raw || isNaN(raw)) {
                // 空值：桌面铺满、平板/手机回退默认
                state.devWidth[dev] = null;
                if (dev === 'desktop') { stage.style.maxWidth = ''; }
                else { stage.style.maxWidth = (dev === 'tablet' ? 834 : 390) + 'px'; }
                return;
            }
            var w = Math.max(280, Math.min(2400, raw));
            state.devWidth[dev] = w;
            stage.style.maxWidth = w + 'px';
        }
        root.querySelectorAll('.xf-diy-devices button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyDevice(btn.getAttribute('data-dev') || 'desktop');
            });
        });
        var devWidthInput = root.querySelector('.xf-diy-dev-width');
        if (devWidthInput) { devWidthInput.addEventListener('input', applyDevWidth); }

        // 画布缩放
        function setZoom(v) {
            state.zoom = Math.max(50, Math.min(150, Math.round(v)));
            stage.style.zoom = state.zoom / 100;
            var zv = root.querySelector('.xf-diy-zoom-val');
            if (zv) { zv.textContent = state.zoom + '%'; }
        }
        root.querySelectorAll('[data-zoom]').forEach(function (b) {
            b.addEventListener('click', function () {
                var a = b.getAttribute('data-zoom');
                if (a === 'in') { setZoom(state.zoom + 10); }
                else if (a === 'out') { setZoom(state.zoom - 10); }
                else { setZoom(100); }
            });
        });
        applyDevice('desktop');

        var btnUndo = root.querySelector('.xf-diy-btn-undo');
        var btnRedo = root.querySelector('.xf-diy-btn-redo');
        if (btnUndo) { btnUndo.addEventListener('click', undo); }
        if (btnRedo) { btnRedo.addEventListener('click', redo); }

        var btnPreview = root.querySelector('.xf-diy-btn-preview');
        if (btnPreview) {
            btnPreview.addEventListener('click', function () {
                var overlay = document.querySelector('.xf-diy-preview-overlay');
                if (overlay) { closePreview(); btnPreview.classList.remove('active'); }
                else { openPreview(); btnPreview.classList.add('active'); }
            });
        }
        // 预览：独立全屏浮层，克隆当前已渲染内容，桌面铺满 / 平板·手机按宽度居中
        function openPreview() {
            if (document.querySelector('.xf-diy-preview-overlay')) { return; }
            var overlay = document.createElement('div');
            overlay.className = 'xf-diy-preview-overlay';
            overlay.innerHTML =
                '<div class="xf-diy-preview-bar">'
                + '<span class="xf-diy-preview-title"><i class="ti ti-eye"></i> 预览</span>'
                + '<div class="xf-diy-preview-devices">'
                + '<button type="button" data-pdev="desktop">桌面</button>'
                + '<button type="button" data-pdev="tablet">平板</button>'
                + '<button type="button" data-pdev="mobile">手机</button>'
                + '</div>'
                + '<label class="xf-diy-preview-width">宽度 <input type="number" min="280" max="2400" class="xf-diy-preview-winput"> px</label>'
                + '<span class="xf-diy-preview-spacer"></span>'
                // 关闭预览按钮：始终位于预览栏右侧，窄屏下随预览栏自动换行也不会被裁切，
                // 用户点击后即可返回舞台编辑界面（亦可按 Esc 关闭）。
                + '<button type="button" class="xf-diy-preview-close" title="返回舞台编辑（Esc）"><i class="ti ti-arrow-left"></i> 退出预览</button>'
                + '</div>'
                + '<div class="xf-diy-preview-scroll"><div class="xf-diy-preview-canvas"></div></div>';
            document.body.appendChild(overlay);
            var canvas = overlay.querySelector('.xf-diy-preview-canvas');
            // 克隆当前舞台已渲染内容（保留组件预览结果，无需重新 fetch）
            var frag = document.createDocumentFragment();
            Array.prototype.forEach.call(stage.childNodes, function (n) {
                if (n.nodeType === 1 && n.classList && n.classList.contains('xf-diy-stage-empty')) { return; }
                frag.appendChild(n.cloneNode(true));
            });
            canvas.appendChild(frag);
            // 对克隆内容重新执行组件初始化（countUp / 图表等依赖 JS 的组件在舞台已初始化过，
            // 克隆体是全新 DOM，需在此重新 scan 才能正常显示与交互）。
            // 预览浮层克隆体是稳定 DOM，临时关闭编辑器占位标记，让图表/地图真实渲染
            try { if (XF.scan) { var __e = window.__xfDiyEdit; window.__xfDiyEdit = false; XF.scan(canvas); window.__xfDiyEdit = __e; } } catch (e) {}
            // 扫描瞬间容器可能尚未布局（宽度为 0），echarts 会据此锁定 0 宽导致图表不可见；
            // 布局稳定后派发 resize 事件，触发 echarts/apex 的 resize 处理器按真实宽度重绘。
            function xfDiyResizeCharts() {
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () { try { window.dispatchEvent(new Event('resize')); } catch (e) {} });
                });
            }
            xfDiyResizeCharts();
            var dev = state.device || 'desktop';
            function setPreviewDev(d) {
                dev = d;
                state.device = d;
                canvas.style.width = '';
                canvas.style.maxWidth = '';
                canvas.className = 'xf-diy-preview-canvas xf-diy-dev-' + d;
                overlay.querySelectorAll('.xf-diy-preview-devices button').forEach(function (b) {
                    b.classList.toggle('active', b.getAttribute('data-pdev') === d);
                });
                var wi = overlay.querySelector('.xf-diy-preview-winput');
                var cur = state.devWidth[d];
                wi.value = (cur === null || cur === undefined) ? '' : cur;
                root.querySelectorAll('.xf-diy-devices button').forEach(function (b) {
                    b.classList.toggle('active', b.getAttribute('data-dev') === d);
                });
                xfDiyResizeCharts();
            }
            setPreviewDev(dev);
            overlay.querySelectorAll('.xf-diy-preview-devices button').forEach(function (b) {
                b.addEventListener('click', function () { setPreviewDev(b.getAttribute('data-pdev')); });
            });
            var wi = overlay.querySelector('.xf-diy-preview-winput');
            wi.addEventListener('input', function () {
                var raw = parseInt(wi.value, 10);
                if (!raw || isNaN(raw)) {
                    state.devWidth[dev] = null;
                    canvas.style.width = '';
                    canvas.style.maxWidth = '';
                    canvas.className = 'xf-diy-preview-canvas xf-diy-dev-' + dev;
                } else {
                    var v = Math.max(280, Math.min(2400, raw));
                    state.devWidth[dev] = v;
                    canvas.style.width = v + 'px';
                    canvas.style.maxWidth = '100%';
                    canvas.className = 'xf-diy-preview-canvas';
                    xfDiyResizeCharts();
                }
            });
            overlay.querySelector('.xf-diy-preview-close').addEventListener('click', closePreview);
            overlay._xfKey = function (e) { if (e.key === 'Escape') { closePreview(); } };
            document.addEventListener('keydown', overlay._xfKey);
        }
        function closePreview() {
            var overlay = document.querySelector('.xf-diy-preview-overlay');
            if (!overlay) { return; }
            if (overlay._xfKey) { document.removeEventListener('keydown', overlay._xfKey); }
            overlay.remove();
            // 退出后让编辑态舞台与预览所选设备保持一致
            applyDevice(state.device || 'desktop');
        }

        var btnExport = root.querySelector('.xf-diy-btn-export');
        var btnImport = root.querySelector('.xf-diy-btn-import');
        var btnClear = root.querySelector('.xf-diy-btn-clear');
        var btnCode = root.querySelector('.xf-diy-btn-code');
        var btnSample = root.querySelector('.xf-diy-btn-sample');
        var btnSave = root.querySelector('.xf-diy-btn-save');
        var btnHelp = root.querySelector('.xf-diy-btn-help');
        if (btnExport) { btnExport.addEventListener('click', doExport); }
        if (btnImport) { btnImport.addEventListener('click', doImport); }
        if (btnClear) {
            btnClear.addEventListener('click', function () {
                if (!confirm('确定清空舞台上的全部组件？此操作可撤销。')) { return; }
                state.tree = [];
                state.byId = {};
                state.selected = null;
                renderStage();
                props.innerHTML = emptyPropsHtml();
                pushHistory();
            });
        }
        if (btnCode) { btnCode.addEventListener('click', generateCode); }
        if (btnSample) { btnSample.addEventListener('click', loadSample); }
        function loadSample() {
            if (state.tree.length && !confirm('载入内置示例将覆盖当前舞台内容（可撤销），继续？')) { return; }
            state.tree = (SAMPLE_TREE || []).map(function (b) { return cloneTree(b); });
            state.byId = {}; markIds(state.tree);
            renderStage().then(function () { props.innerHTML = emptyPropsHtml(); pushHistory(); });
            toast('已载入示例布局');
        }
        if (btnSave) {
            btnSave.addEventListener('click', function () { saveLocal(state.tree); toast('已保存到本地草稿，下次打开自动恢复'); });
        }
        if (btnHelp) { btnHelp.addEventListener('click', showHelp); }

        function showHelp() {
            var body = '<div class="xf-diy-help">'
                + '<div class="row g-3">'
                + '<div class="col-md-6"><div class="xf-diy-help-card"><i class="ti ti-plus"></i><h6>添加组件</h6><p>从左侧列表<b>点击</b>或<b>拖拽</b>组件 / 模板 / 行 / 列到舞台；也可在空舞台点击「载入示例」。</p></div></div>'
                + '<div class="col-md-6"><div class="xf-diy-help-card"><i class="ti ti-arrows-move"></i><h6>布局与嵌套</h6><p>把组件拖入<b>栅格行 / 列</b>内实现任意嵌套；选中列可<b>拆分</b>为 2/3/4 列或<b>添加同级列</b>。</p></div></div>'
                + '<div class="col-md-6"><div class="xf-diy-help-card"><i class="ti ti-sliders"></i><h6>编辑属性</h6><p>点击舞台中的任意块，在右侧面板修改<b>全部配置参数</b>（含颜色 / 图标 / 图片 / 链接 / 候选项）。</p></div></div>'
                + '<div class="col-md-6"><div class="xf-diy-help-card"><i class="ti ti-device-desktop-analytics"></i><h6>预览</h6><p>用顶栏切换<b>桌面 / 平板 / 手机</b>预览，或开启<b>预览模式</b>隐藏编辑栏查看真实效果。</p></div></div>'
                + '<div class="col-md-6"><div class="xf-diy-help-card"><i class="ti ti-history"></i><h6>撤销 / 重做</h6><p>所有操作均可<b>撤销 / 重做</b>；自动保存到本地草稿，下次打开自动恢复。</p></div></div>'
                + '<div class="col-md-6"><div class="xf-diy-help-card"><i class="ti ti-code"></i><h6>生成代码</h6><p>点击「生成代码」可一键导出 <b>Laravel / ThinkPHP</b> 控制器与 HTML 视图，直接复制使用。</p></div></div>'
                + '</div>'
                + '<div class="xf-diy-help-keys">快捷键：<b>Ctrl+Z</b> 撤销 · <b>Ctrl+Y</b> 重做 · <b>Ctrl+D</b> 复制 · <b>Ctrl+C/V</b> 复制/粘贴 · <b>Del</b> 删除 · <b>↑/↓</b> 移动 · <b>Esc</b> 取消选择</div>'
                + '</div>';
            openModal('使用帮助', body, [{ text: '知道了', cls: 'btn btn-primary', act: function (m) { m.remove(); } }]);
        }

        function doExport() {
            fetch(state.renderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf()
                },
                body: JSON.stringify({ mode: 'export', tree: state.tree })
            }).then(function (r) { return r.text(); }).then(function (html) {
                var code = typeof html === 'string' ? html : JSON.stringify(html);
                openModal('导出 HTML', '<p class="text-muted small">以下是完整可独立运行的页面源码，可复制到项目中直接使用，或下载为 .html 文件。</p>'
                    + '<textarea class="xf-diy-code" readonly spellcheck="false">' + esc(code) + '</textarea>',
                    [
                        { text: '下载 .html', cls: 'btn btn-success', act: function (m) { downloadHtml(code); } },
                        { text: '复制', cls: 'btn btn-primary', act: function (m) { copyText(m.querySelector('textarea').value); } },
                        { text: '新窗口预览', cls: 'btn btn-soft-secondary', act: function () {
                            var w = window.open('', '_blank');
                            w.document.open(); w.document.write(code); w.document.close();
                        } },
                        { text: '关闭', cls: 'btn btn-light', act: function (m) { m.remove(); } }
                    ]);
            });
        }

        function downloadHtml(code) {
            try {
                var blob = new Blob([code], { type: 'text/html;charset=utf-8' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url; a.download = 'diy-layout.html';
                document.body.appendChild(a); a.click(); a.remove();
                setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
            } catch (e) { toast('下载失败：' + e.message, 'danger'); }
        }

        function doImport() {
            openModal('导入 JSON', '<p class="text-muted small">粘贴此前导出的块树 JSON（或 localStorage 草稿），将替换当前舞台内容。</p>'
                + '<textarea class="xf-diy-code" placeholder="粘贴 JSON 块树…" spellcheck="false"></textarea>',
                [
                    { text: '导入', cls: 'btn btn-primary', act: function (m) {
                        try {
                            var t = JSON.parse(m.querySelector('textarea').value);
                            state.tree = []; state.byId = {};
                            (t || []).forEach(function (b) { state.byId[b.id || newId()] = b; if (b.children) { markIds(b.children); } });
                            renderStage().then(function () { props.innerHTML = emptyPropsHtml(); pushHistory(); });
                            m.remove();
                        } catch (e) { alert('JSON 解析失败：' + e.message); }
                    } },
                    { text: '取消', cls: 'btn btn-light', act: function (m) { m.remove(); } }
                ]);
        }
        function markIds(list) { (list || []).forEach(function (b) { if (!b.id) { b.id = newId(); } state.byId[b.id] = b; if (b.children) { markIds(b.children); } }); }

        // ---------- 代码生成（Laravel / ThinkPHP / 块树 / HTML）----------
        function buildHtmlFromStage() {
            var out = '';
            var blocks = stage.querySelectorAll(':scope > .xf-diy-block');
            for (var i = 0; i < blocks.length; i++) { out += blockToHtml(blocks[i], 2); }
            return out.trim() ? out.trim() + '\n' : '<!-- 舞台为空：请先拖入组件 -->';
        }
        function blockToHtml(el, depth) {
            if (!el.classList || !el.classList.contains('xf-diy-block')) { return ''; }
            var pad = new Array(depth + 1).join('  ');
            var kind = el.getAttribute('data-kind');
            var body = el.querySelector(':scope > .xf-diy-block-body');
            if (kind === 'component') {
                if (!body) { return ''; }
                if (body.querySelector('.xf-diy-empty-preview')) { return ''; } // 编辑器中未填充的占位不导出
                var content = body.innerHTML.trim();
                return content ? pad + content.replace(/\n/g, '\n' + pad) + '\n' : '';
            }
            var container = body ? body.children[0] : null;
            if (!container) { return ''; }
            var cls;
            if (container.classList.contains('xf-diy-col')) {
                // 列宽来自 data-colclass（编辑态不挂 Bootstrap 类，避免 flex 行换行）
                cls = 'col ' + (container.getAttribute('data-colclass') || '').trim();
            } else {
                cls = container.className
                    .replace('xf-diy-row', 'row')
                    .replace('drag-over', '').replace('xf-diy-col-empty', '')
                    .replace(/\s+/g, ' ').trim();
            }
            var inner = '';
            for (var i = 0; i < container.children.length; i++) {
                var c = container.children[i];
                if (c.classList && c.classList.contains('xf-diy-block')) { inner += blockToHtml(c, depth + 1); }
            }
            return pad + '<div class="' + cls + '">\n' + inner + pad + '</div>\n';
        }

        function generateCode() {
            var html = buildHtmlFromStage();
            var safeHtml = html.indexOf('<?') !== -1 ? html.replace(/<\?/g, '&lt;?') : html; // 避免 PHP 短标签被解析
            var blocksJson = JSON.stringify(state.tree, null, 4);
            var laravel = laravelController(safeHtml);
            var tp = thinkphpController(safeHtml);
            var tab = function (id, label, file, active) {
                return '<li class="nav-item"><a class="nav-link' + (active ? ' active' : '') + '" data-tab="' + id + '" role="tab">'
                    + label + (file ? '<span class="xf-diy-code-ext">' + file + '</span>' : '') + '</a></li>';
            };
            var pane = function (id, active, content) {
                return '<div class="tab-pane' + (active ? ' active' : '') + '" data-pane="' + id + '">'
                    + '<textarea class="xf-diy-code-ta" readonly spellcheck="false">' + esc(content) + '</textarea></div>';
            };
            var body = '<div class="xf-diy-code-head">'
                + '<div class="xf-diy-code-desc">由当前舞台布局一键生成可运行页面代码，复制后直接用于项目。各框架均已包含组件依赖声明与渲染入口。</div>'
                + '<div class="xf-diy-code-meta">共 <b>' + state.tree.length + '</b> 个顶层块'
                + (html.indexOf('<?') !== -1 ? ' · <span class="text-warning">已转义 &lt;? 短标签</span>' : '') + '</div></div>'
                + '<ul class="nav nav-tabs xf-diy-code-tabs" role="tablist">'
                + tab('html', 'HTML 视图', '.blade.php', true) + tab('laravel', 'Laravel 控制器', 'Controller.php')
                + tab('tp', 'ThinkPHP 控制器', 'Controller.php') + tab('json', '块树 JSON', '.json') + '</ul>'
                + '<div class="tab-content xf-diy-code-body">'
                + pane('html', true, html) + pane('laravel', '', laravel) + pane('tp', '', tp) + pane('json', '', blocksJson)
                + '</div>';
            var m = openModal('生成代码', body, [
                { text: '复制当前', cls: 'btn btn-primary', act: function (mm) { var ta = mm.querySelector('.tab-pane.active textarea'); if (ta) { copyText(ta.value); } } },
                { text: '下载文件', cls: 'btn btn-success', act: function (mm) { var ta = mm.querySelector('.tab-pane.active textarea'); if (!ta) { return; } var id = mm.querySelector('.xf-diy-code-tabs .nav-link.active').getAttribute('data-tab'); downloadText(ta.value, id === 'html' ? 'diy_page.blade.php' : (id === 'json' ? 'diy_blocks.json' : 'DiyPageController.php')); } },
                { text: '关闭', cls: 'btn btn-light', act: function (mm) { mm.remove(); } }
            ]);
            if (m) {
                m.querySelectorAll('.xf-diy-code-tabs .nav-link').forEach(function (a) {
                    a.addEventListener('click', function () {
                        var t = a.getAttribute('data-tab');
                        m.querySelectorAll('.xf-diy-code-tabs .nav-link').forEach(function (x) { x.classList.toggle('active', x === a); });
                        m.querySelectorAll('.xf-diy-code-body .tab-pane').forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-pane') === t); });
                    });
                });
            }
        }

        function laravelController(html) {
            return '<?php\n\nnamespace App\\Http\\Controllers;\n\nuse zxf\\XfAdmin\\XfAdmin;\n\n/**\n * 由 DIY 可视化布局器自动生成的页面控制器\n * 依赖：composer require zxf/xfadmin\n */\nclass DiyPageController extends Controller\n{\n    public function index()\n    {\n        // 以下为可视化布局器渲染出的页面源码\n        $html = <<<\'HTML\'\n'
                + html + '\nHTML;\n\n        return XfAdmin::page([\n            \'title\'   => \'自定义页面\',\n            \'content\' => $html,\n        ]);\n    }\n}\n';
        }

        function thinkphpController(html) {
            return '<?php\n\nnamespace app\\controller;\n\nuse think\\Response;\n\n/**\n * 由 DIY 可视化布局器自动生成的页面控制器\n */\nclass DiyPageController\n{\n    public function index()\n    {\n        // 以下为可视化布局器渲染出的页面源码\n        $html = <<<\'HTML\'\n'
                + html + '\nHTML;\n\n        return Response::create($html, 200, [\n            \'Content-Type\' => \'text/html; charset=utf-8\',\n        ]);\n    }\n}\n';
        }

        function downloadText(text, name) {
            try {
                var blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url; a.download = name;
                document.body.appendChild(a); a.click(); a.remove();
                setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
            } catch (e) { toast('下载失败：' + e.message, 'danger'); }
        }

        // ---------- 模态框 ----------
        function openModal(title, bodyHtml, buttons) {
            var mask = document.createElement('div');
            mask.className = 'xf-diy-modal-mask';
            var foot = '';
            (buttons || []).forEach(function (b, i) { foot += '<button class="' + b.cls + '" data-bi="' + i + '">' + esc(b.text) + '</button>'; });
            mask.innerHTML = '<div class="xf-diy-modal"><div class="xf-diy-modal-head"><span>' + esc(title) + '</span>'
                + '<div class="xf-diy-modal-tools">'
                + '<button class="xf-diy-modal-max" data-max="1" title="最大化 / 还原"><i class="ti ti-maximize"></i><i class="ti ti-minimize d-none"></i></button>'
                + '<button class="xf-diy-modal-close" data-close="1" title="关闭 (Esc)">&times;</button></div>'
                + '</div>'
                + '<div class="xf-diy-modal-body">' + bodyHtml + '</div>'
                + '<div class="xf-diy-modal-foot">' + foot + '</div></div>';
            document.body.appendChild(mask);
            requestAnimationFrame(function () { mask.classList.add('show'); });
            mask.addEventListener('click', function (e) {
                if (e.target.closest('[data-max]')) {
                    var m = mask.querySelector('.xf-diy-modal');
                    var on = m.classList.toggle('maximized');
                    m.querySelector('.ti-maximize').classList.toggle('d-none', on);
                    m.querySelector('.ti-minimize').classList.toggle('d-none', !on);
                    return;
                }
                if (e.target === mask || e.target.closest('[data-close]')) { mask.remove(); }
            });
            (buttons || []).forEach(function (b, i) {
                var btn = mask.querySelector('[data-bi="' + i + '"]');
                if (btn) { btn.addEventListener('click', function () { b.act(mask); }); }
            });
            return mask;
        }

        function copyText(text) {
            if (navigator.clipboard) { navigator.clipboard.writeText(text).then(function () { toast('已复制到剪贴板'); }); }
            else {
                var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); toast('已复制到剪贴板'); } catch (e) {} ta.remove();
            }
        }
        function toast(msg, variant) {
            variant = variant || 'success';
            if (state.XF && state.XF.toast) { state.XF.toast({ body: msg, variant: variant }); return; }
            showLocalToast(msg, variant);
        }
        function showLocalToast(msg, variant) {
            var box = root.querySelector('.xf-diy-toasts');
            if (!box) { box = document.createElement('div'); box.className = 'xf-diy-toasts'; root.appendChild(box); }
            var icons = { success: 'ti-circle-check', danger: 'ti-alert-triangle', warning: 'ti-alert-circle', info: 'ti-info-circle' };
            var t = document.createElement('div');
            t.className = 'xf-diy-toast xf-diy-toast-' + variant;
            t.innerHTML = '<i class="ti ' + (icons[variant] || icons.success) + '"></i><span>' + esc(msg) + '</span>';
            box.appendChild(t);
            requestAnimationFrame(function () { t.classList.add('show'); });
            setTimeout(function () {
                t.classList.remove('show');
                setTimeout(function () { if (t.parentNode) { t.parentNode.removeChild(t); } }, 280);
            }, 2200);
        }

        // ---------- 键盘快捷键 ----------
        function isTyping() {
            var a = document.activeElement;
            return !!(a && (a.tagName === 'INPUT' || a.tagName === 'TEXTAREA' || a.tagName === 'SELECT' || a.isContentEditable));
        }
        root.addEventListener('keydown', function (e) {
            var meta = e.ctrlKey || e.metaKey;
            var k = (e.key || '').toLowerCase();
            if (meta && (k === 'z' || k === 'y')) {
                if (isTyping()) { return; } // 输入框内保留原生撤销
                e.preventDefault();
                if (k === 'y' || e.shiftKey) { redo(); } else { undo(); }
                return;
            }
            if (meta && (k === 'd' || k === 'c' || k === 'v')) {
                if (isTyping()) { return; } // 输入框内保留原生复制/粘贴/快捷键
                if (k === 'd' && state.selected) { e.preventDefault(); duplicateBlock(state.selected); }
                else if (k === 'c' && state.selected) { e.preventDefault(); copyBlock(state.selected); }
                else if (k === 'v') { e.preventDefault(); pasteBlock(); }
                return;
            }
            if (meta && k === 's') { e.preventDefault(); saveLocal(state.tree); toast('已保存到本地草稿（Ctrl+S）'); return; }
            if (isTyping()) { return; }
            if ((e.key === 'Delete' || e.key === 'Backspace') && state.selected) { e.preventDefault(); removeBlock(state.selected); }
            else if (e.key === 'ArrowUp' && state.selected) { e.preventDefault(); moveBlock(state.selected, 'up'); }
            else if (e.key === 'ArrowDown' && state.selected) { e.preventDefault(); moveBlock(state.selected, 'down'); }
            else if (e.key === 'Escape') {
                if (document.querySelector('.xf-diy-modal-mask')) { return; } // 弹窗优先处理 Esc
                state.selected = null;
                stage.querySelectorAll('.xf-diy-block.selected').forEach(function (b) { b.classList.remove('selected'); });
                props.innerHTML = emptyPropsHtml();
            }
        });
        // 弹窗（附加在 body 外）需独立监听 Esc 关闭
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { var m = document.querySelector('.xf-diy-modal-mask'); if (m) { m.remove(); } }
        });

        // ---------- 启动 ----------
        seedHistory();
        renderStage();
    }

    // ---------- 小工具 ----------
    function debounce(fn, ms) {
        var t = null;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }
    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function cssEsc(s) {
        if (window.CSS && CSS.escape) { return CSS.escape(s); }
        return String(s).replace(/"/g, '\\"');
    }

    function boot() { init(); }
    if (window.XFAdmin) {
        boot();
    } else {
        // 核心运行时可能晚于本脚本加载：轮询等待其就绪后再初始化
        var _wait = setInterval(function () {
            if (window.XFAdmin) { clearInterval(_wait); boot(); }
        }, 30);
        document.addEventListener('DOMContentLoaded', function () {
            if (window.XFAdmin) { clearInterval(_wait); boot(); }
        });
    }
})();
