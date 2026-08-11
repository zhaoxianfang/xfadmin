// 轻量验证脚本：检查 topbar-badge top 值、表单 remote 提交、LoadingButton 忙碌态。
import { chromium } from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';

const BASE = 'http://demo.wsf.local';

async function req(page, method, url, data) {
  return page.evaluate(async ({ method, url, data }) => {
    const r = await fetch(url, {
      method, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: data ? JSON.stringify(data) : undefined,
    });
    return { status: r.status, ok: r.ok, text: await r.text() };
  }, { method, url, data });
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', e => errors.push('PAGEERROR: ' + e.message));
  page.on('console', m => { if (m.type() === 'error') errors.push('CONSOLE: ' + m.text()); });

  // 拦截外部请求，避免阻塞
  await page.route('**/*', route => {
    const u = route.request().url();
    if (u.includes('127.0.0.1') || u.includes('demo.wsf.local') || u.startsWith('data:')) return route.continue();
    return route.abort();
  });

  // 1) topnav 页：检查 topbar-badge computed top
  await page.goto(BASE + '/admin/demo/preview/topnav', { waitUntil: 'networkidle' }).catch(()=>{});
  const badgeTop = await page.evaluate(() => {
    const el = document.querySelector('.app-topbar .topbar-menu .topbar-item .topbar-badge');
    if (!el) return 'NO_BADGE';
    return getComputedStyle(el).top;
  });
  console.log('TOPBAR_BADGE_TOP =', badgeTop, '(期望 2px，非 -10px)');

  // 2) 登录页：检查表单 action 指向 /admin/login
  await page.goto(BASE + '/admin/demo/page/auth', { waitUntil: 'networkidle' }).catch(()=>{});
  const loginAction = await page.evaluate(() => {
    const f = document.querySelector('form[action*="login"]');
    return f ? f.getAttribute('action') : 'NO_FORM';
  });
  console.log('LOGIN_FORM_ACTION =', loginAction, '(期望 /admin/login)');

  // 3) demo forms 页：表单 remote 提交到 mock 端点
  await page.goto(BASE + '/admin/demo/forms', { waitUntil: 'networkidle' }).catch(()=>{});
  const formInfo = await page.evaluate(() => {
    const forms = Array.from(document.querySelectorAll('form[data-xf-remote]'));
    return forms.map(f => ({ action: f.getAttribute('action'), remote: f.hasAttribute('data-xf-remote') }));
  });
  console.log('REMOTE_FORMS =', JSON.stringify(formInfo));

  // 4) 模拟提交第一个 remote 表单
  let submitResult = 'SKIP';
  if (formInfo.length) {
    const action = formInfo[0].action;
    const res = await req(page, 'POST', action, { name: 'test', email: 'a@b.com', password: 'x' });
    submitResult = res.status + ' | ' + (res.text.slice(0, 80));
  }
  console.log('FORM_SUBMIT_RESULT =', submitResult);

  // 5) LoadingButton 类存在
  const lbtn = await page.evaluate(() => {
    const b = document.querySelector('.xf-lbtn');
    return b ? b.className : 'NO_LBTN';
  });
  console.log('LOADING_BTN_CLASS =', lbtn);

  console.log('ERRORS =', errors.length ? errors.join(' | ') : 'NONE');
  await browser.close();
})();
