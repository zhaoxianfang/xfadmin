// TopNav 预览页真实浏览器自测（自包含 HTTP 模式）
// 脚本内部启动 wsf 临时服务（绕过登录守卫渲染 /topnav），测试完自动关闭。
import pw from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
import { spawn } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const { chromium } = pw;

const WSF = '/Users/aha/www/wsf';
const PORT = 8099;
const ROUTER = path.join(WSF, '.topnav_preview_router.php');

// 临时 router：直接渲染 preview 并托管 xfadmin 资源
writeFileSync(ROUTER, `<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri === '/topnav') {
    require '${WSF}/vendor/autoload.php';
    $app = require '${WSF}/bootstrap/app.php';
    $kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
    $kernel->bootstrap();
    $request = Illuminate\\Http\\Request::create('http://demo.wsf.local/admin/demo/preview/topnav', 'GET');
    $app->instance('request', $request);
    session()->start();
    session(['admin_user' => ['id' => 1, 'name' => '演示管理员', 'username' => 'demo', 'avatar' => null]]);
    view()->share('errors', new Illuminate\\Support\\ViewErrorBag());
    $ctrl = $app->make(Modules\\Admin\\Http\\Controllers\\Web\\DemoController::class);
    echo (string) $ctrl->preview('topnav', $request);
    return;
}
if (preg_match('#^/zxf/xfadmin/(.*)$#', $uri, $m)) {
    $file = '${WSF}/vendor/zxf/xfadmin/resources/assets/' . $m[1];
    if (is_file($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mime = ['js'=>'application/javascript','css'=>'text/css','png'=>'image/png','svg'=>'image/svg+xml','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf'][$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($file);
        return;
    }
    http_response_code(404); echo 'not found'; return;
}
http_response_code(404); echo '404';
`);

const srv = spawn('php', ['-S', `127.0.0.1:${PORT}`, ROUTER], { cwd: WSF, stdio: 'ignore' });
await new Promise(r => setTimeout(r, 1500));

const BASE = `http://127.0.0.1:${PORT}`;
const errors = [], consoleErrors = [], broken = [], failedLocal = [];
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();
page.on('pageerror', e => errors.push(String(e)));
page.on('console', m => { if (m.type() === 'error') consoleErrors.push(m.text()); });
page.on('response', r => { if (r.status() >= 400 && r.url().startsWith(BASE)) failedLocal.push(r.url() + ' ' + r.status()); });
await page.route('**/*', route => route.request().url().startsWith(BASE) ? route.continue() : route.abort());

await page.goto(BASE + '/topnav', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(800);

const overflow = await page.evaluate(() => { const de = document.scrollingElement; return { scrollW: de.scrollWidth, clientW: de.clientWidth }; });
const hasHOverflow = overflow.scrollW > overflow.clientW + 2;

const imgs = await page.$$eval('img', els => els.map(e => ({ src: e.currentSrc || e.src, nw: e.naturalWidth })));
for (const im of imgs) if (im.nw === 0) broken.push(im.src);

const appItem = await page.$('text=应用');
let cascadeVisible = false;
if (appItem) {
  const li = await appItem.evaluateHandle(el => el.closest('li.nav-item'));
  await li.hover();
  await page.waitForTimeout(400);
  cascadeVisible = await li.evaluate(el => {
    const d = el.querySelector(':scope > .dropdown-menu');
    if (!d) return false;
    const cs = getComputedStyle(d);
    const r = d.getBoundingClientRect();
    return cs.display !== 'none' && r.height > 10 && r.top >= 0 && r.top < window.innerHeight;
  });
}

const ecomItem = await page.$('text=电商');
let megaVisible = false;
if (ecomItem) {
  const li = await ecomItem.evaluateHandle(el => el.closest('li.nav-item'));
  await li.hover();
  await page.waitForTimeout(400);
  megaVisible = await li.evaluate(el => {
    const m = el.querySelector(':scope > .dropdown-menu-xxl') || el.querySelector(':scope > .dropdown-menu');
    if (!m) return false;
    const cs = getComputedStyle(m);
    const r = m.getBoundingClientRect();
    return cs.display !== 'none' && r.height > 20 && r.top >= 0 && r.top < window.innerHeight;
  });
}

// 桌面端右侧搜索框（问题6）：不应收缩为 0×0，且不遮挡左侧菜单
let searchWidthOk = false;
{
  const r = await page.evaluate(() => {
    const b = document.querySelector('.app-topbar .topnav-search.d-lg-flex');
    if (!b) return { found: false };
    const cs = getComputedStyle(b);
    const rect = b.getBoundingClientRect();
    return { found: true, display: cs.display, w: Math.round(rect.width), h: Math.round(rect.height) };
  });
  searchWidthOk = r.found && r.display !== 'none' && r.w >= 180 && r.h >= 28;
}

await page.setViewportSize({ width: 480, height: 900 });
await page.waitForTimeout(400);
const toggle = await page.$('.topnav-toggle-button');
let accordionWorks = false;
if (toggle) {
  await toggle.click();
  await page.waitForTimeout(500);
  const expanded = await page.evaluate(() => { const c = document.getElementById('topnav-menu-content'); return c ? c.classList.contains('show') : false; });
  const deep = await page.$('text=多级菜单');
  if (deep && expanded) {
    await deep.click();
    await page.waitForTimeout(400);
    accordionWorks = await page.evaluate(() => {
      const open = [...document.querySelectorAll('.topnav .navbar-nav .nav-item .dropdown-menu.show')];
      return open.some(m => m.offsetParent !== null && m.getBoundingClientRect().height > 10);
    });
  }
}

await browser.close();
srv.kill();
rmSync(ROUTER);

const realConsole = consoleErrors.filter(e => !/Failed to load resource|ERR_|net::/.test(e));
const result = {
  pageErrors: errors, consoleErrors: realConsole, failedLocal, brokenImgs: broken,
  horizontalOverflow: hasHOverflow ? overflow : false,
  desktopCascade: cascadeVisible, megaPanel: megaVisible, mobileAccordion: accordionWorks,
  searchBoxWidth: searchWidthOk,
  pass: errors.length === 0 && realConsole.length === 0 && broken.length === 0 && !hasHOverflow && cascadeVisible && megaVisible && accordionWorks && searchWidthOk,
};
console.log(JSON.stringify(result, null, 2));
process.exit(result.pass ? 0 : 1);
