/* svgdotjs 全局隔离护栏（后置）
 * apexsankey 已在其 UMD 加载时捕获 @svgdotjs/svg.js v3（闭包持有，不再依赖全局）。
 * 这里把 v3 转存到 window.svgdotjs 供开发者按需使用，并恢复加载前的 window.SVG
 * （如 apexcharts 内置版本），彻底消除同页共存时的全局冲突。 */
(function () {
    window.svgdotjs = window.SVG;
    if (window.__xfPrevSVG !== undefined) {
        window.SVG = window.__xfPrevSVG;
    } else {
        // var 声明的全局绑定不可 delete，回退置 undefined
        try { delete window.SVG; } catch (e) { /* noop */ }
        if (typeof window.SVG !== 'undefined') { window.SVG = undefined; }
    }
    try { delete window.__xfPrevSVG; } catch (e) { window.__xfPrevSVG = undefined; }
})();
