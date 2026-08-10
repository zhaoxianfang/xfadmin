/**
 * 后台模板一致性自测（Template Parity Selftest）
 * ----------------------------------------------------------------------------
 * 目的：确保本扩展包渲染出的后台页面，其【布局计算样式】与【交互行为】
 *       与 INSPINIA 原版后台模板（HTML/Full/dist）保持一致。
 *
 * 背景：本包内置了模板原版 app.min.css / app.js / config.js。
 *       若在 xfadmin.css 中重写框架布局类，或在 xfadmin.js 中重复绑定
 *       .sidenav-toggle-button 等元素，会覆盖/抵消模板的真实实现，
 *       导致侧栏宽度、顶栏定位、折叠态、抽屉遮罩等与模板不一致。
 *       本脚本用于回归防护。
 *
 * 用法：
 *   node tools/selftest/template_parity.mjs <模板URL> <本包URL>
 * 例：
 *   php -S 127.0.0.1:8931 demo/router.php &
 *   (cd <INSPINIA>/HTML/Full/dist && php -S 127.0.0.1:8932) &
 *   node tools/selftest/template_parity.mjs \
 *        http://127.0.0.1:8932/index.html http://127.0.0.1:8931/
 *
 * 依赖：playwright（本机位于 /Users/aha/www/xfeditor/node_modules/playwright）
 */
import { chromium } from '/Users/aha/www/xfeditor/node_modules/playwright/index.mjs';

const TPL = process.argv[2] || 'http://127.0.0.1:8932/index.html';
const PKG = process.argv[3] || 'http://127.0.0.1:8931/';

/** 需逐项比对计算样式的布局类（这些必须完全由模板 app.min.css 决定） */
const LAYOUT = [
  ['.sidenav-menu', ['position', 'width', 'backgroundColor', 'zIndex']],
  ['.app-topbar', ['position', 'height', 'marginLeft', 'zIndex']],
  ['.content-page', ['marginLeft']],
  ['.side-nav-link', ['padding', 'fontSize']],
  ['.side-nav-title', ['fontSize', 'textTransform', 'letterSpacing']],
];

function newPage(browser) {
  return browser.newPage({ viewport: { width: 1440, height: 900 } });
}

async function prepare(p, url, errs) {
  p.on('pageerror', e => errs.push('PAGEERROR: ' + e.message));
  p.on('console', m => {
    const t = m.text();
    // 离线环境下外部资源被 abort 属预期，不计为错误
    if (m.type() === 'error' && !/ERR_FAILED|Failed to load resource/.test(t)) errs.push('CONSOLE: ' + t.slice(0, 140));
  });
  await p.route('**/*', r => (r.request().url().startsWith('http://127.0.0.1') ? r.continue() : r.abort()));
  await p.goto(url, { waitUntil: 'domcontentloaded' });
  await p.waitForTimeout(1500);
  // 关闭模板首页自动弹出的推广 offcanvas/modal，避免遮罩拦截点击
  await p.evaluate(() => {
    document.querySelectorAll('.offcanvas.show').forEach(o => o.classList.remove('show'));
    document.querySelectorAll('.offcanvas-backdrop, .modal-backdrop').forEach(b => b.remove());
    document.querySelectorAll('.modal.show').forEach(m => { m.classList.remove('show'); m.style.display = 'none'; });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
  });
  await p.waitForTimeout(200);
}

async function styles(p) {
  return p.evaluate(L => {
    const out = {};
    for (const [sel, props] of L) {
      const el = document.querySelector(sel);
      if (!el) { out[sel] = null; continue; }
      const cs = getComputedStyle(el);
      out[sel] = Object.fromEntries(props.map(k => [k, cs[k]]));
    }
    return out;
  }, LAYOUT);
}

/** 交互行为探针：返回各行为是否符合模板语义 */
async function behavior(p) {
  const r = {};
  r.hasSidenavSize = await p.evaluate(() => document.documentElement.hasAttribute('data-sidenav-size'));

  const before = await p.getAttribute('html', 'data-sidenav-size');
  const toggle = await p.$('.sidenav-toggle-button');
  r.toggleExists = !!toggle;
  if (toggle) {
    await toggle.click();
    await p.waitForTimeout(500);
    const after = await p.getAttribute('html', 'data-sidenav-size');
    r.toggleChanges = before !== after;
    r.toggleTrace = `${before} -> ${after}`;
    r.condensedWidth = await p.evaluate(() => Math.round(document.querySelector('.sidenav-menu')?.getBoundingClientRect().width || 0));
    await toggle.click();
    await p.waitForTimeout(500);
    r.toggleRestores = (await p.getAttribute('html', 'data-sidenav-size')) === before;
  }

  const sub = await p.$('.side-nav .side-nav-link[data-bs-toggle="collapse"]');
  r.submenuIsCollapse = !!sub;
  if (sub) {
    const sel = await sub.getAttribute('href');
    const a = await p.evaluate(s => !!document.querySelector(s)?.classList.contains('show'), sel);
    await sub.click();
    await p.waitForTimeout(700);
    const b = await p.evaluate(s => !!document.querySelector(s)?.classList.contains('show'), sel);
    r.submenuToggles = a !== b;
  }

  const themeBtn = await p.$('#light-dark-mode');
  r.themeBtnExists = !!themeBtn;
  if (themeBtn) {
    const t0 = await p.getAttribute('html', 'data-bs-theme');
    await themeBtn.click();
    await p.waitForTimeout(500);
    r.themeToggles = t0 !== (await p.getAttribute('html', 'data-bs-theme'));
  }

  await p.setViewportSize({ width: 700, height: 900 });
  await p.waitForTimeout(800);
  r.smallScreenOffcanvas = (await p.getAttribute('html', 'data-sidenav-size')) === 'offcanvas';
  const tg = await p.$('.sidenav-toggle-button');
  if (tg) {
    await tg.click();
    await p.waitForTimeout(600);
    r.drawerOpens = await p.evaluate(() => document.documentElement.classList.contains('sidebar-enable'));
    r.drawerBackdrop = await p.evaluate(() => !!document.getElementById('custom-backdrop'));
  }
  return r;
}

const browser = await chromium.launch();
const tplErrs = [], pkgErrs = [];
const tplPage = await newPage(browser), pkgPage = await newPage(browser);
await prepare(tplPage, TPL, tplErrs);
await prepare(pkgPage, PKG, pkgErrs);

const tplStyles = await styles(tplPage);
const pkgStyles = await styles(pkgPage);
const tplBehavior = await behavior(tplPage);
const pkgBehavior = await behavior(pkgPage);
await browser.close();

let fails = 0;
const line = (okv, msg, extra = '') => {
  if (!okv) fails++;
  console.log(`${okv ? 'PASS' : 'FAIL'}  ${msg}${extra ? '   ' + extra : ''}`);
};

console.log('=== 布局计算样式一致性（以模板为基准） ===');
for (const [sel] of LAYOUT) {
  const a = tplStyles[sel], b = pkgStyles[sel];
  if (!a) { console.log(`SKIP  ${sel}（模板页无此元素）`); continue; }
  if (!b) { line(false, `${sel} 在本包页面缺失`); continue; }
  const diff = Object.keys(a).filter(k => a[k] !== b[k]);
  line(diff.length === 0, sel, diff.length ? diff.map(k => `${k}: tpl=${a[k]} pkg=${b[k]}`).join('; ') : '');
}

console.log('\n=== 交互行为一致性 ===');
const BEHAVIORS = [
  ['hasSidenavSize', 'html[data-sidenav-size] 由 app.js 建立'],
  ['toggleExists', '.sidenav-toggle-button 存在'],
  ['toggleChanges', '单击切换折叠态（无重复绑定抵消）'],
  ['toggleRestores', '再次单击可还原'],
  ['submenuIsCollapse', '子菜单使用 Bootstrap Collapse'],
  ['submenuToggles', '子菜单可展开/收起'],
  ['themeBtnExists', '#light-dark-mode 存在'],
  ['themeToggles', '明暗主题可切换'],
  ['smallScreenOffcanvas', '窄屏自动进入 offcanvas'],
  ['drawerOpens', '窄屏抽屉可打开(sidebar-enable)'],
  ['drawerBackdrop', '窄屏出现模板遮罩 #custom-backdrop'],
];
for (const [k, label] of BEHAVIORS) {
  const t = tplBehavior[k], g = pkgBehavior[k];
  if (t === undefined) { console.log(`SKIP  ${label}（模板未覆盖）`); continue; }
  line(t === g, label, t === g ? '' : `tpl=${t} pkg=${g}${k === 'toggleChanges' ? ` (${tplBehavior.toggleTrace} / ${pkgBehavior.toggleTrace})` : ''}`);
}
line(
  Math.abs((tplBehavior.condensedWidth || 0) - (pkgBehavior.condensedWidth || 0)) <= 2,
  '折叠态侧栏宽度与模板一致',
  `tpl=${tplBehavior.condensedWidth} pkg=${pkgBehavior.condensedWidth}`
);

console.log('\n=== JS 错误 ===');
console.log('模板:', tplErrs.length ? tplErrs.slice(0, 5) : 'none');
console.log('本包:', pkgErrs.length ? pkgErrs.slice(0, 5) : 'none');
if (pkgErrs.length) fails += pkgErrs.length;

console.log(fails === 0 ? '\nTEMPLATE PARITY: PASS' : `\nTEMPLATE PARITY: FAIL (${fails})`);
process.exit(fails === 0 ? 0 : 1);
