import pw from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
import { spawn } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const { chromium } = pw;
const WSF = '/Users/aha/www/wsf';
const PORT = 8063;
const ROUTER = path.join(WSF, 'domain_ops_router_tmp.php');
const B = String.fromCharCode(92);
const NS = 'Modules' + B + 'Admin' + B + 'Http' + B + 'Controllers' + B + 'Web' + B + 'EnterpriseController';
const ILL = 'Illuminate' + B + 'Contracts' + B + 'Http' + B + 'Kernel';
const REQ = 'Illuminate' + B + 'Http' + B + 'Request';
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
    view()->share('errors', new Illuminate${B}Support${B}ViewErrorBag());
    echo (string) $app->make(${JSON.stringify(NS)})->show('ticket', 'list');
    return;
}
if (preg_match('#^/admin/app/ticket/list/detail/(\\d+)#', $uri, $m)) {
    require ${JSON.stringify(WSF + '/vendor/autoload.php')};
    $app = require ${JSON.stringify(WSF + '/bootstrap/app.php')};
    $kernel = $app->make(${JSON.stringify(ILL)}::class);
    $kernel->bootstrap();
    $request = ${JSON.stringify(REQ)}::create('http://demo.wsf.local' . $uri, 'GET');
    $app->instance('request', $request);
    session()->start();
    session(['admin_user' => ['id' => 1, 'name' => '演示管理员', 'username' => 'demo', 'avatar' => null]]);
    view()->share('errors', new Illuminate${B}Support${B}ViewErrorBag());
    echo (string) $app->make(${JSON.stringify(NS)})->recordDetail('ticket', 'list', (int) $m[1]);
    return;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/admin/api/enterprise/op') {
    header('Content-Type: application/json');
    echo json_encode(['code' => 200, 'ok' => true, 'data' => ['message' => 'ok']]);
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
let errors = [];
const failedRes = [];
const localFailed = [];
page.on('pageerror', e => { errors.push('PAGEERR ' + String(e)); console.log('PAGEERR', String(e)); });
page.on('console', m => {
    if (m.type() === 'error') {
        // 外部资源被 route.abort 拦截产生的 ERR_FAILED 属预期（离线测试），不计为错误
        if (/Failed to load resource: net::ERR_FAILED/.test(m.text())) return;
        errors.push('CONSOLE ' + m.text()); console.log('CONSOLE', m.text());
    }
});
page.on('requestfailed', r => { if (r.url().startsWith(BASE)) { localFailed.push(r.url() + ' ' + (r.failure()?.errorText || '')); } });
page.on('response', r => { if (r.status() >= 400) { failedRes.push(r.status() + ' ' + r.url()); } });
await page.route('**/*', route => route.request().url().startsWith(BASE) ? route.continue() : route.abort());

// ===== 1. 看板卡片领域按钮 =====
await page.goto(BASE + '/admin/app/ticket/list', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(2500);
const kanbanOpsRaw = await page.$$eval('.xf-kanban-card', cards => {
    return cards.map(c => Array.from(c.querySelectorAll('[data-xf-op]')).map(b => b.textContent.trim()));
});
const kanbanOps = kanbanOpsRaw.filter(x => x.length > 0);
console.log('看板卡片领域按钮(首卡):', JSON.stringify(kanbanOps[0] || []));
const hasKanbanOp = kanbanOps.some(ops => ops.some(t => t.includes('派发') || t.includes('认领') || t.includes('回复')));
console.log('看板有领域按钮:', hasKanbanOp ? 'YES ✅' : 'NO ❌');

// 点击一个领域按钮（认领）→ 应触发 op API（此处 stub 404，检查无 JS 崩溃）
if (kanbanOps.length && kanbanOps[0].length) {
    const firstOpBtn = await page.$('.xf-kanban-card [data-xf-op]');
    if (firstOpBtn) {
        await firstOpBtn.click();
        await page.waitForTimeout(600);
    }
}

// ===== 2. 详情页操作面板 =====
await page.goto(BASE + '/admin/app/ticket/list/detail/1', { waitUntil: 'load', timeout: 30000 });
await page.waitForTimeout(2000);
const opLabels = await page.$$eval('.xf-op-panel [data-xf-op], [data-xf-op]', els => Array.from(els).map(e => e.textContent.trim()));
console.log('详情操作面板按钮:', JSON.stringify(opLabels));
const hasDetailOps = ['派发','认领','转派','回复','评价'].every(k => opLabels.some(t => t.includes(k)));
console.log('详情操作面板完整:', hasDetailOps ? 'YES ✅' : 'NO ❌');
const hasPromptAttr = await page.$$eval('.xf-op-panel [data-xf-prompt]', els => els.length);
console.log('有 prompt 输入按钮:', hasPromptAttr > 0 ? 'YES ✅ (' + hasPromptAttr + ')' : 'NO ❌');

// ===== 3. 图片回复对话框（带 data-xf-attach 的回复按钮 → promptFields）=====
const attachBtn = await page.$('.xf-op-panel [data-xf-op][data-xf-attach="1"]');
console.log('存在图片回复按钮:', attachBtn ? 'YES ✅' : 'NO ❌');
let attachFlow = false;
if (attachBtn) {
    const hasModalEl = await page.$('#xf-prompt-fields-modal');
    const bsModalOk = await page.evaluate(() => typeof window.bootstrap !== 'undefined' && !!window.bootstrap.Modal);
    const swalOk = await page.evaluate(() => typeof window.Swal !== 'undefined');
    const promptFieldsOk = await page.evaluate(() => typeof window.XFAdmin !== 'undefined' && typeof XFAdmin.promptFields === 'function');
    console.log('诊断: bootstrap.Modal=' + bsModalOk + ' Swal=' + swalOk + ' promptFields=' + promptFieldsOk + ' modalEl=' + (hasModalEl ? 'pre-exists' : 'none'));
    await attachBtn.click();
    await page.waitForTimeout(800);
    // Bootstrap Modal 回退路径（页面未加载 SweetAlert2 时）
    const modalVisible = await page.$('#xf-prompt-fields-modal.show, .modal.show#xf-prompt-fields-modal');
    const modalFields = await page.$$('#xf-prompt-fields-modal .modal-body input, #xf-prompt-fields-modal .modal-body textarea');
    const swalVisible = await page.$('.swal2-container.swal2-shown');
    const dialogKind = swalVisible ? 'swal' : (modalVisible ? 'modal' : 'none');
    console.log('图片回复对话框类型:', dialogKind, '| 字段数:', modalFields.length);
    if (modalFields.length >= 2) {
        // 填内容 + 图片 URL → 提交
        await page.fill('#xf-prompt-fields-modal #xf-pfm-0', '附现场照片说明');
        await page.fill('#xf-prompt-fields-modal #xf-pfm-1', 'images/users/user-2.jpg');
        await page.click('#xf-prompt-fields-modal .xf-pf-ok');
        await page.waitForTimeout(1200);
        const timelineImgs = await page.$$eval('img[src*="user-2.jpg"]', els => els.map(e => e.getAttribute('src') || '').slice(0, 5));
        console.log('页面 user-2.jpg 图片:', JSON.stringify(timelineImgs));
        const firstTlItem = await page.evaluate(() => {
            const it = document.querySelector('.timeline .timeline-item, .xf-timeline .xf-timeline-item');
            return it ? it.outerHTML.slice(0, 400) : 'none';
        });
        console.log('首条时间线节点HTML:', firstTlItem);
        const tlInfo = await page.evaluate(() => {
            const tls = Array.from(document.querySelectorAll('.timeline, .xf-timeline')).map(t => t.className);
            const items = document.querySelectorAll('.timeline-item, .xf-timeline-item').length;
            return { tls: tls.slice(0, 3), items: items };
        });
        console.log('时间线容器:', JSON.stringify(tlInfo));
        const timelineImg = await page.$eval('.timeline img[src*="images/users/user-2.jpg"], .xf-timeline img[src*="images/users/user-2.jpg"], .timeline-item img[src*="user-2.jpg"]', el => true).catch(() => false);
        console.log('时间线出现图片回复缩略图:', timelineImg ? 'YES ✅' : 'NO ❌');
        attachFlow = !!timelineImg;
    } else {
        console.log('图片回复对话框字段不足 ❌');
    }
}

// ===== 4. 断言 =====
const pass = hasKanbanOp && hasDetailOps && hasPromptAttr > 0 && !!attachBtn && attachFlow && errors.length === 0 && localFailed.length === 0;
console.log('');
console.log('PASS:', pass ? 'YES ✅' : 'NO ❌', '| JS错误:', errors.length, '| 本地请求失败:', localFailed.length);
console.log('4xx响应(' + failedRes.length + '):');
Array.from(new Set(failedRes)).slice(0, 15).forEach(f => console.log('  ' + f));
console.log('本地请求失败:');
Array.from(new Set(localFailed)).slice(0, 10).forEach(f => console.log('  ' + f));
await browser.close();
srv.kill();
rmSync(ROUTER);
process.exit(pass ? 0 : 1);
