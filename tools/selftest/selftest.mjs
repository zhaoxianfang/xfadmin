// selftest.mjs —— 端到端 Playwright 自测
//
// 加载组件总览页与每个组件独立页，检查：
//   1) 浏览器 pageerror（未捕获 JS 异常）
//   2) console error（控制台错误）
//   3) 本地（127.0.0.1）资源 4xx/5xx 失败
//   4) 破图（img.naturalWidth === 0 且非外链）
//   5) 文档横向溢出（document.scrollingElement.scrollWidth > clientWidth）
//
// 外部请求（非 127.0.0.1）一律 route.abort，保证离线可跑。
//
// 运行：bash run.sh （内部先 build.php → xss_audit.php → asset_check.php → php -S → node selftest.mjs）
// 环境变量：BASE 默认 http://127.0.0.1:8903

import { chromium } from '/Users/aha/www/xfeditor/node_modules/playwright/index.mjs';

const BASE = process.env.BASE || 'http://127.0.0.1:8903';

const results = {
  pages: 0,
  pageErrors: 0,
  consoleErrors: 0,
  failedLocal: 0,
  brokenImgs: 0,
  docErrors: 0,
  details: [],
};

function record(page, issue, info) {
  results.details.push(`[${page}] ${issue}: ${info}`);
}

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });

// 拦截外部请求（离线）：非本机一律 route.fulfill 占位响应。
// 注意不能 abort —— abort 会触发浏览器 "Failed to load resource: net::ERR_FAILED"
// 的 console.error 污染结果。图片类返回 1x1 透明 PNG（OSM 瓦片等可正常解码），
// 其余（Google Fonts @import 等）返回空 200，消除网络层错误。
const TRANSPARENT_PNG = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
  'base64'
);
await ctx.route('**/*', (route) => {
  const url = route.request().url();
  if (url.startsWith('http://127.0.0.1:8903') || url.startsWith('http://localhost')) {
    return route.continue();
  }
  const req = route.request();
  if (req.resourceType() === 'image' || req.resourceType() === 'media') {
    return route.fulfill({ status: 200, contentType: 'image/png', body: TRANSPARENT_PNG });
  }
  return route.fulfill({ status: 200, contentType: 'text/plain', body: '' });
});

const page = await ctx.newPage();

page.on('pageerror', (err) => {
  results.pageErrors++;
  record(page.url(), 'pageerror', err.message);
});
page.on('console', (msg) => {
  if (msg.type() === 'error') {
    results.consoleErrors++;
    record(page.url(), 'console.error', msg.text());
  }
});
page.on('response', (resp) => {
  const u = resp.url();
  if (u.startsWith('http://127.0.0.1:8903') && resp.status() >= 400) {
    // 跳过外部被 abort 的（它们不会到 response）
    results.failedLocal++;
    record(page.url(), 'local ' + resp.status(), u);
  }
});

// 1) 总览页
results.pages++;
await page.goto(`${BASE}/`, { waitUntil: 'networkidle' }).catch(() => {});
await page.waitForTimeout(400);

const horizontalOverflow = await page.evaluate(() => {
  const el = document.scrollingElement;
  return el.scrollWidth - el.clientWidth;
});
if (horizontalOverflow > 2) {
  results.docErrors++;
  record('index', 'horizontal-overflow', `${horizontalOverflow}px`);
}

// 2) 逐个组件独立页
const aliases = await page.evaluate(async () => {
  const r = await fetch('/doc_index.json');
  const j = await r.json();
  // doc_index.json 为扁平结构：顶层键即组件别名
  return Object.keys(j).filter((k) => k !== 'page' || typeof j[k] === 'string');
});

for (const alias of aliases) {
  results.pages++;
  const url = `${BASE}/doc/${alias}`;
  try {
    await page.goto(url, { waitUntil: 'networkidle' }).catch(() => {});
    await page.waitForTimeout(150);

    // 破图检测
    const broken = await page.evaluate(() => {
      let n = 0;
      document.querySelectorAll('img').forEach((img) => {
        const src = img.getAttribute('src') || '';
        if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:')) return;
        if (img.complete && img.naturalWidth === 0) n++;
      });
      return n;
    });
    if (broken > 0) {
      results.brokenImgs += broken;
      record(alias, 'broken-img', `${broken}`);
    }

    // 横向溢出
    const ov = await page.evaluate(() => document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth);
    if (ov > 2) {
      results.docErrors++;
      record(alias, 'horizontal-overflow', `${ov}px`);
    }
  } catch (e) {
    results.pageErrors++;
    record(alias, 'goto-failed', e.message);
  }
}

await browser.close();

// 汇总
const failed = results.pageErrors + results.consoleErrors + results.failedLocal + results.brokenImgs + results.docErrors;
console.log(`\n=== XfAdmin 端到端自测 ===`);
console.log(`检查页面: ${results.pages}`);
console.log(`pageerror: ${results.pageErrors}`);
console.log(`console.error: ${results.consoleErrors}`);
console.log(`本地资源失败: ${results.failedLocal}`);
console.log(`破图: ${results.brokenImgs}`);
console.log(`文档错误(溢出等): ${results.docErrors}`);

if (results.details.length) {
  console.log(`\n--- 明细（前 30 条）---`);
  for (const d of results.details.slice(0, 30)) console.log(d);
}

if (failed > 0) {
  console.log(`\nRESULT: FAIL (${failed} 项问题)`);
  process.exit(1);
} else {
  console.log(`\nRESULT: PASS (${results.pages} 页面，0 问题)`);
  process.exit(0);
}
