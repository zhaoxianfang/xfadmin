import pw from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
import { spawn } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const { chromium } = pw;
const WSF = '/Users/aha/www/wsf';
const PORT = 8088;
const ROUTER = path.join(WSF, '.dt_dropdown_router.php');
const B = String.fromCharCode(92);
const NS = 'Modules' + B + 'Admin' + B + 'Http' + B + 'Controllers' + B + 'Web' + B;
const ILL = 'Illuminate' + B + 'Contracts' + B + 'Http' + B + 'Kernel';
const REQ = 'Illuminate' + B + 'Http' + B + 'Request';
const routerPhp = `<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$ns = ${JSON.stringify(NS)};
if ($uri === '/admin/system/admins' || $uri === '/admin/users') {
    require ${JSON.stringify(WSF + '/vendor/autoload.php')};
    $app = require ${JSON.stringify(WSF + '/bootstrap/app.php')};
    $kernel = $app->make(${JSON.stringify(ILL)}::class);
    $kernel->bootstrap();
    $request = ${JSON.stringify(REQ)}::create('http://demo.wsf.local' . $uri, 'GET');
    $app->instance('request', $request);
    session()->start();
    session(['admin_user' => ['id' => 1, 'name' => '演示管理员', 'username' => 'demo', 'avatar' => null]]);
    view()->share('errors', new Illuminate\\Support\\ViewErrorBag());
    if ($uri === '/admin/system/admins') { echo (string) $app->make($ns . 'SystemController')->admins(); return; }
    echo (string) $app->make($ns . 'UserController')->users(); return;
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
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();
page.on('pageerror', e => console.log('PAGEERR', String(e)));
page.on('console', m => { if (m.type()==='error') console.log('CONSOLE', m.text()); });
await page.route('**/*', route => {
  const u = route.request().url();
  if (u.startsWith(BASE)) return route.continue();
  return route.abort();
});
// mock data api（server_side 表格请求 /admin/api/data/xxx）
await page.route('**/admin/api/data/**', route => {
  const rows = [];
  for (let i=0;i<8;i++){ rows.push({id:i+1,username:'admin'+i,email:'a'+i+'@x.com',name:'管理员'+i,role:['管理员'],status:'active',ip:'127.0.0.1',last_login:'2026-08-13',created:'2026-08-01'}); }
  route.fulfill({ status:200, contentType:'application/json', body: JSON.stringify({draw:1,recordsTotal:8,recordsFiltered:8,data:rows}) });
});

async function testPage(url, label) {
  await page.goto(BASE + url, { waitUntil: 'load', timeout: 30000 });
  await page.waitForSelector('tbody tr', { timeout: 8000 }).catch(()=>{});
  await page.waitForTimeout(1500);
  const moreBtn = await page.$('.dropdown .dropdown-toggle:has-text("更多")');
  const rows = await page.$$eval('tbody tr', r => r.length).catch(()=>0);
  if (!moreBtn) { console.log(label, '更多按钮未找到 行数=', rows); return; }
  await moreBtn.click();
  await page.waitForTimeout(800);
  const info = await page.evaluate(() => {
    const btn = [...document.querySelectorAll('.dropdown-toggle')].find(b => b.textContent.includes('更多'));
    if (!btn) return { found:false };
    const menu = btn.nextElementSibling;
    if (!menu) return { found:true, menu:false };
    const cs = getComputedStyle(menu);
    const r = menu.getBoundingClientRect();
    return { position: cs.position, zIndex: cs.zIndex, display: cs.display, top: Math.round(r.top), left: Math.round(r.left), width: Math.round(r.width), height: Math.round(r.height), inViewport: r.top>=0 && r.bottom<=window.innerHeight && r.left>=0 && r.right<=window.innerWidth, hasSticky: btn.closest('.xf-dt-sticky')!==null, scrollBody: !!document.querySelector('.dt-scroll-body') };
  });
  console.log(label, JSON.stringify(info));
}
await testPage('/admin/system/admins', '管理员列表');
await testPage('/admin/users', '用户列表');
await browser.close();
srv.kill();
rmSync(ROUTER);
