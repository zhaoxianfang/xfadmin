import pw from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
import { spawn } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const { chromium } = pw;
const WSF = '/Users/aha/www/wsf';
const PORT = 8062;
const ROUTER = path.join(WSF, '.kanban_e2e_router.php');
const B = String.fromCharCode(92);
const NS = 'Modules' + B + 'Admin' + B + 'Http' + B + 'Controllers' + B + 'Web' + B + 'EnterpriseController';
const ILL = 'Illuminate' + B + 'Contracts' + B + 'Http' + B + 'Kernel';
const REQ = 'Illuminate' + B + 'Http' + B + 'Request';
const routerPhp = `<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$ns = ${JSON.stringify(NS)};
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
    echo (string) $app->make($ns)->show('ticket', 'list');
    return;
}
if (preg_match('#^/admin/api/data/(.*)$#', $uri, $m)) {
    // 透传到真实 Kernel 的 data api
    header('Content-Type: application/json');
    echo json_encode(['draw'=>1,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[]]);
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
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
page.on('pageerror', e => console.log('PAGEERR', String(e)));
page.on('console', m => { if (m.type()==='error') console.log('CONSOLE', m.text()); });
await page.route('**/*', route => route.request().url().startsWith(BASE) ? route.continue() : route.abort());
await page.goto(BASE + '/admin/app/ticket/list', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(2500);
const before = await page.$$eval('.xf-kanban-col', cols => cols.map(c => ({ col: c.getAttribute('data-column'), count: c.querySelector('.xf-kanban-count')?.textContent })));
console.log('拖拽前计数:', JSON.stringify(before));
// 真实 Sortable 拖拽：取第一列第一个卡片，拖到第二列
const handles = await page.$$('.xf-kanban-col:first-child .xf-kanban-card');
if (handles.length === 0) { console.log('无卡片'); await browser.close(); srv.kill(); rmSync(ROUTER); process.exit(0); }
const src = await handles[0].boundingBox();
const dstCol = await page.$('.xf-kanban-col:nth-child(2) .xf-kanban-body');
const dst = await dstCol.boundingBox();
// 鼠标拖拽序列
await page.mouse.move(src.x + src.width/2, src.y + src.height/2);
await page.mouse.down();
await page.mouse.move(src.x + src.width/2 + 20, src.y + src.height/2 + 20, { steps: 5 });
await page.mouse.move(dst.x + dst.width/2, dst.y + dst.height/2, { steps: 10 });
await page.mouse.move(dst.x + dst.width/2, dst.y + dst.height/2, { steps: 5 });
await page.mouse.up();
await page.waitForTimeout(800);
const after = await page.$$eval('.xf-kanban-col', cols => cols.map(c => ({ col: c.getAttribute('data-column'), count: c.querySelector('.xf-kanban-count')?.textContent })));
console.log('拖拽后计数:', JSON.stringify(after));
const changed = before[0].count !== after[0].count && before[1].count !== after[1].count;
console.log('计数是否更新:', changed ? 'YES ✅' : 'NO ❌');
await browser.close();
srv.kill();
rmSync(ROUTER);
