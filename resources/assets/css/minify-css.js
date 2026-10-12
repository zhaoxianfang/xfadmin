// 安全的 CSS 压缩器（占位符防碰撞版）
// 策略：用 \u0001<index>\u0001 占位符保护括号内容（calc/var/url/rgba 等），
// 该占位符不含空格、不会与真实数值 " 0 " 混淆，也不会被空白折叠破坏。
const fs = require('fs');
const target = process.argv[2];
if (!target) { console.error('用法: node minify-css.js <input.css>'); process.exit(1); }

let css = fs.readFileSync(target, 'utf8');
// 1) 去除注释（保留 /*! */）
css = css.replace(/\/\*(?!!)[\s\S]*?\*\//g, '');

// 2) 抽取括号内容（平衡括号，支持一层嵌套），用占位符保护
const pockets = [];
css = css.replace(/\(([^()]*(\([^()]*\)[^()]*)*)\)/g, (m) => {
  pockets.push(m);
  return '' + (pockets.length - 1) + '';
});

// 3) 折叠空白为单空格
css = css.replace(/\s+/g, ' ');

// 4) 移除结构性空白（保留冒号后的空格以兼容 a :hover 等后代选择器语义）
css = css.replace(/\s*\{\s*/g, '{');
css = css.replace(/\s*\}\s*/g, '}');
css = css.replace(/\s*;\s*/g, ';');
css = css.replace(/\s*,\s*/g, ',');
css = css.replace(/\s*>\s*/g, '>');
css = css.replace(/\s*\+\s*/g, '+');
css = css.replace(/\s*~\s*/g, '~');

// 5) 还原括号内容
css = css.replace(/(\d+)/g, (m, i) => pockets[Number(i)]);

css = css.trim();
process.stdout.write(css);
