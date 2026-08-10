/* svgdotjs 全局隔离护栏（前置）
 * 背景：apexcharts 内置旧版 svg.js（含 draggable 等私有插件）同样占用 window.SVG；
 * @svgdotjs/svg.js v3 以 var SVG=... 方式加载会覆写它，二者同页共存时
 * apexcharts 会抛 "selectionRect.draggable is not a function" / "parser Error"。
 * 此处先暂存加载前的 window.SVG，待 apexsankey 捕获 v3 后由后置护栏恢复。 */
(function () { window.__xfPrevSVG = window.SVG; })();
