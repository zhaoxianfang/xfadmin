import pkg from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
const { chromium } = pkg;
import { readFileSync } from 'node:fs';

/**
 * 浏览器级自测（Playwright 无头 Chromium）：
 *   加载总览页 all.html 与每个组件独立页 doc_<alias>.html，
 *   检查 JS 致命错误 / 控制台错误 / 本地包资源 404 / 包资源破图 / 横向溢出。
 *
 * 仅「包内资源」（路径含 /zxf/xfadmin）计入失败；演示占位图等非包缺陷忽略。
 * 运行：node tools/selftest/selftest.mjs
 */

const BASE = process.env.SELFTEST_BASE || 'http://127.0.0.1:8901';
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

const idx = JSON.parse(readFileSync(new URL('./.build/doc_index.json', import.meta.url), 'utf8'));
const aliases = idx.aliases || [];

const browser = await chromium.launch({
  executablePath: CHROME,
  args: ['--no-sandbox', '--disable-gpu'],
});
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });

const stats = {
  pageErrors: 0,
  consoleErrors: 0,
  failedLocal: 0,
  brokenImgs: 0,
  overflow: 0,
  docErrors: {},
};

// 全局聚合计数（仅包内资源 / 真正的 JS 错误）
page.on('pageerror', () => { stats.pageErrors++; });
page.on('console', (m) => {
  if (m.type() !== 'error') return;
  const t = m.text();
  // 资源加载失败（外网被主动 abort / 本地 404）不计入 JS 错误，
  // 前者忽略、后者由下方 response 状态码统计
  if (/Failed to load resource/i.test(t)) return;
  stats.consoleErrors++;
});
// 本地包资源 4xx/5xx（404 是已完成的响应，requestfailed 不会触发，故用 response 拦截）
page.on('response', (r) => {
  const u = r.url();
  if (u.startsWith(BASE) && u.includes('/zxf/xfadmin') && r.status() >= 400) {
    stats.failedLocal++;
  }
});

// 外网请求一律拦截，离线可跑
await page.route('**/*', (route) => {
  const u = route.request().url();
  if (u.startsWith(BASE)) return route.continue();
  return route.abort();
});

async function visit(url, label) {
  const errs = [];
  const onPageErr = (e) => errs.push('pageerror: ' + e.message);
  const onConsole = (m) => {
    if (m.type() !== 'error') return;
    if (/Failed to load resource/i.test(m.text())) return;
    errs.push('console: ' + m.text());
  };
  page.on('pageerror', onPageErr);
  page.on('console', onConsole);
  try {
    await page.goto(url, { waitUntil: 'load', timeout: 30000 });
  } catch (e) {
    errs.push('goto: ' + e.message);
  }
  await page.waitForTimeout(250);

  const broken = await page.$$eval('img', (imgs) => imgs.filter((i) => {
    const s = i.getAttribute('src') || '';
    return s.indexOf('/zxf/xfadmin') === 0 && i.complete && i.naturalWidth === 0;
  }).length).catch(() => 0);
  if (broken > 0) {
    stats.brokenImgs += broken;
    errs.push('brokenImgs: ' + broken);
  }

  const ov = await page.evaluate(() => {
    const el = document.scrollingElement;
    return el ? el.scrollWidth - el.clientWidth : 0;
  }).catch(() => 0);
  if (ov > 0) {
    stats.overflow++;
    errs.push('overflow: ' + ov);
  }

  page.off('pageerror', onPageErr);
  page.off('console', onConsole);
  if (errs.length) stats.docErrors[label] = errs.slice(0, 6);
}

await visit(BASE + '/all', 'all');
console.error('[1/' + (aliases.length + 1) + '] all');

for (let i = 0; i < aliases.length; i++) {
  const a = aliases[i];
  await visit(BASE + '/doc/' + encodeURIComponent(a), a);
  console.error('[' + (i + 2) + '/' + (aliases.length + 1) + '] ' + a);
}

await browser.close();

console.log(JSON.stringify(stats, null, 2));
const ok = stats.pageErrors === 0 && stats.consoleErrors === 0 && stats.failedLocal === 0
  && stats.brokenImgs === 0 && stats.overflow === 0 && Object.keys(stats.docErrors).length === 0;
console.log(ok ? 'SELFTEST PASSED' : 'SELFTEST FAILED');
process.exit(ok ? 0 : 1);
