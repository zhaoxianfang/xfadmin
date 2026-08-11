// 验证：1) 后台侧边栏菜单含「顶部导航 TopNav」入口（页面与布局分组）
//       2) TopNav 预览页徽标 .topbar-badge 定位 top:-6px/right:6px/padding:6px + .topbar-item 相对定位
//       3) 移动端/小屏幕下 .topbar-search 宽度 100%
import pw from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
import { spawn } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const { chromium } = pw;

const WSF = '/Users/aha/www/wsf';
const PORT = 8085;
const ROUTER = path.join(WSF, '.topnav_menu_router.php');
const CTRL = 'Modules\\Admin\\Http\\Controllers\\Web\\DemoController';

writeFileSync(ROUTER, `<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri === '/pages') { $path = '/admin/demo/pages'; $method = 'demoPages'; $arg = null; }
elseif ($uri === '/topnav') { $path = '/admin/demo/preview/topnav'; $method = 'preview'; $arg = 'topnav'; }
else { $path = '/admin/demo/pages'; $method = 'demoPages'; $arg = null; }
if (preg_match('#^/zxf/xfadmin/(.*)$#', $uri, $m)) {
    $file = '${WSF}/vendor/zxf/xfadmin/resources/assets/' . $m[1];
    if (is_file($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mime = ['js'=>'application/javascript','css'=>'text/css','png'=>'image/png','svg'=>'image/svg+xml','jpg'=>'image/jpeg','gif'=>'image/gif','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf'][$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime); readfile($file); return;
    }
    http_response_code(404); echo 'not found'; return;
}
require '${WSF}/vendor/autoload.php';
$app = require '${WSF}/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
$kernel->bootstrap();
$request = Illuminate\\Http\\Request::create('http://demo.wsf.local' . $path, 'GET');
$app->instance('request', $request);
session()->start();
session(['admin_user' => ['id' => 1, 'name' => '演示管理员', 'username' => 'demo', 'avatar' => null]]);
view()->share('errors', new Illuminate\\Support\\ViewErrorBag());
$ctrl = $app->make(${CTRL}::class);
try { echo (string) ($arg !== null ? $ctrl->$method($arg) : $ctrl->$method()); } catch (\\Throwable $e) { echo "ERR: ".$e->getMessage(); }
`);

const srv = spawn('php', ['-S', `127.0.0.1:${PORT}`, ROUTER], { cwd: WSF, stdio: 'ignore' });
await new Promise(r => setTimeout(r, 1500));
const BASE = `http://127.0.0.1:${PORT}`;

const errors = [], consoleErrors = [];
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();
page.on('pageerror', e => errors.push(String(e)));
page.on('console', m => { if (m.type() === 'error') consoleErrors.push(m.text()); });
page.on('response', r => { if (r.status() >= 400 && r.url().startsWith(BASE)) errors.push(r.url() + ' ' + r.status()); });
await page.route('**/*', route => route.request().url().startsWith(BASE) ? route.continue() : route.abort());

await page.goto(BASE + '/pages', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(600);
const menu = await page.evaluate(() => {
  const links = [...document.querySelectorAll('a')];
  const hit = links.find(a => a.textContent.trim() === '顶部导航 TopNav');
  return hit ? { found: true, href: hit.getAttribute('href') } : { found: false };
});

await page.goto(BASE + '/topnav', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(700);
const badge = await page.evaluate(() => {
  const item = document.querySelector('.app-topbar .topbar-menu .topbar-item');
  const b = document.querySelector('.app-topbar .topbar-menu .topbar-item .topbar-badge');
  const r = { itemRelative: false, badge: null };
  if (item) r.itemRelative = getComputedStyle(item).position === 'relative';
  if (b) { const cs = getComputedStyle(b); r.badge = { top: cs.top, right: cs.right, padding: cs.paddingTop + ' ' + cs.paddingRight }; }
  return r;
});

// 小屏幕（992px，lg 边缘）桌面搜索框满宽验证
await page.setViewportSize({ width: 992, height: 900 });
await page.waitForTimeout(400);
const search = await page.evaluate(() => {
  const s = document.querySelector('.app-topbar .topbar-search');
  if (!s) return { found: false };
  const cs = getComputedStyle(s);
  if (cs.display === 'none') return { found: true, visible: false };
  const rect = s.getBoundingClientRect();
  const docW = document.documentElement.clientWidth;
  return { found: true, visible: true, w: Math.round(rect.width), pct: Math.round(rect.width / docW * 100) };
});
// 移动端（480px）展开折叠面板后搜索框满宽验证
await page.setViewportSize({ width: 480, height: 900 });
await page.waitForTimeout(300);
await page.evaluate(() => {
  const btn = document.querySelector('.navbar-toggler, .topnav-toggle-button');
  if (btn && !document.querySelector('#topnav-menu-content.show, #topnav-menu-content.collapse:not(.show)')) {
    try { btn.click(); } catch (e) {}
  }
  const panel = document.querySelector('#topnav-menu-content');
  if (panel && !panel.classList.contains('show')) panel.classList.add('show');
});
await page.waitForTimeout(300);
const searchMobile = await page.evaluate(() => {
  const s = document.querySelector('.topnav-search.d-lg-none .topbar-search');
  if (!s) return { found: false };
  const cs = getComputedStyle(s);
  if (cs.display === 'none') return { found: true, visible: false };
  const rect = s.getBoundingClientRect();
  return { found: true, visible: true, pct: Math.round(rect.width / document.documentElement.clientWidth * 100) };
});
const searchPct = Math.max(search.pct || 0, searchMobile.pct || 0);

await browser.close();
srv.kill(); rmSync(ROUTER);
const realConsole = consoleErrors.filter(e => !/Failed to load|ERR_|net::/.test(e));
const pass = menu.found && menu.href === '/admin/demo/preview/topnav'
  && badge.itemRelative
  && badge.badge && badge.badge.top === '-6px' && badge.badge.right === '6px' && /6px 6px/.test(badge.badge.padding)
  && searchPct >= 80
  && errors.length === 0 && realConsole.length === 0;
console.log(JSON.stringify({ menu, badge, search, searchMobile, searchPct, errors, console: realConsole, pass }, null, 2));
process.exit(pass ? 0 : 1);
