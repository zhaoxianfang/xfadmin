import pw from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
import { spawn } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const { chromium } = pw;
const WSF = '/Users/aha/www/wsf';
const PORT = 8078;
const ROUTER = path.join(WSF, '.kanban_router.php');
const B = String.fromCharCode(92);
const ILL = 'Illuminate' + B + 'Contracts' + B + 'Http' + B + 'Kernel';
const REQ = 'Illuminate' + B + 'Http' + B + 'Request';
const EC = 'Modules' + B + 'Admin' + B + 'Http' + B + 'Controllers' + B + 'Web' + B + 'EnterpriseController';
const routerPhp = `<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri === '/admin/app/ticket/list') {
    require ${JSON.stringify(WSF + '/vendor/autoload.php')};
    $app = require ${JSON.stringify(WSF + '/bootstrap/app.php')};
    $kernel = $app->make(${JSON.stringify(ILL)}::class);
    $kernel->bootstrap();
    $request = ${JSON.stringify(REQ)}::create('http://demo.wsf.local' . $uri, 'GET');
    $app->instance('request', $request);
    session()->start();
    session(['admin_user' => ['id' => 1, 'name' => '演示管理员', 'username' => 'demo', 'avatar' => null]]);
    view()->share('errors', new Illuminate\\Support\\ViewErrorBag());
    echo (string) $app->make(${JSON.stringify(EC)})->ticketList();
    return;
}
if (preg_match('#^/zxf/xfadmin/(.*)$#', $uri, $m)) {
    $file = ${JSON.stringify(WSF + '/vendor/zxf/xfadmin/resources/assets/')} . $m[1];
    if (is_file($file)) { $ext=pathinfo($file,PATHINFO_EXTENSION); $mime=['js'=>'application/javascript','css'=>'text/css','png'=>'image/png','svg'=>'image/svg+xml','jpg'=>'image/jpeg','gif'=>'image/gif','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf'][$ext]??'application/octet-stream'; header('Content-Type: '.$mime); readfile($file); return; }
    http_response_code(404); echo 'nf'; return;
}
http_response_code(404); echo '404';
`;
writeFileSync(ROUTER, routerPhp);
const srv = spawn('php', ['-S', `127.0.0.1:${PORT}`, ROUTER], { cwd: WSF, stdio: 'ignore' });
await new Promise(r => setTimeout(r, 1500));
const BASE = `http://127.0.0.1:${PORT}`;
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1366, height: 900 } });
const page = await ctx.newPage();
page.on('pageerror', e => console.log('PAGEERR', String(e)));
page.on('console', m => { if (m.type()==='error') console.log('CONSOLE', m.text()); });
await page.route('**/*', route => route.request().url().startsWith(BASE) ? route.continue() : route.abort());
await page.goto(BASE + '/admin/app/ticket/list', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(2000);
const before = await page.$$eval('.xf-kanban-col', cols => cols.map(c => ({ col: c.getAttribute('data-column'), count: c.querySelector('.xf-kanban-count')?.textContent, cards: c.querySelectorAll(':scope > .xf-kanban-body > .xf-kanban-card').length })));
console.log('拖拽前:', JSON.stringify(before));
// 用 Sortable API 模拟拖拽：取第一列的第一个卡片拖到第二列
const moved = await page.evaluate(() => {
  const cols = document.querySelectorAll('.xf-kanban-col');
  if (cols.length < 2) return 'only one col';
  const fromBody = cols[0].querySelector('.xf-kanban-body');
  const toBody = cols[1].querySelector('.xf-kanban-body');
  const card = fromBody.querySelector('.xf-kanban-card');
  if (!card) return 'no card';
  const sid = card.getAttribute('data-item');
  // 触发 Sortable 的 onEnd 逻辑：直接移动 DOM 并 dispatch xf.kanban.move
  const ev = new Event('xf-kanban-test');
  // 直接调用 Sortable 内部不可得，手动移动 + 触发 XFAdmin 注册逻辑
  toBody.appendChild(card);
  // 手动派发 move 事件以模拟 emitMove（前端 updateCounts 监听该事件? 不，updateCounts 在 onEnd 内调用）
  // 改为调用 Sortable 公开的 onEnd：通过原生 dragend 不可靠，这里直接触发 xfadmin 的更新
  document.querySelector('.xf-kanban').dispatchEvent(new CustomEvent('xf.kanban.move', { detail: { item: sid?JSON.parse(sid):{}, from: cols[0].getAttribute('data-column'), to: cols[1].getAttribute('data-column'), fromIndex: 0, toIndex: 0, card } }));
  return 'moved';
});
await page.waitForTimeout(500);
const after = await page.$$eval('.xf-kanban-col', cols => cols.map(c => ({ col: c.getAttribute('data-column'), count: c.querySelector('.xf-kanban-count')?.textContent, cards: c.querySelectorAll(':scope > .xf-kanban-body > .xf-kanban-card').length })));
console.log('模拟move事件后:', JSON.stringify(after));
await browser.close();
srv.kill();
rmSync(ROUTER);
