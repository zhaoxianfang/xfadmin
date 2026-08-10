import pkg from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
const { chromium } = pkg;

const BASE = 'http://127.0.0.1:8900';
const pages = ['/', '/widgets', '/apps', '/tables', '/forms', '/charts'];
const widths = [1920, 1440, 1280, 1024, 992, 768, 576, 414, 375];

const browser = await chromium.launch();

for (const w of widths) {
  const ctx = await browser.newContext({ viewport: { width: w, height: 900 } });
  const page = await ctx.newPage();
  await page.route('**/*', (route) => {
    const u = route.request().url();
    if (u.startsWith(BASE) || u.startsWith('data:') || u.startsWith('blob:')) return route.continue();
    return route.abort();
  });
  console.log(`\n===== WIDTH ${w} =====`);
  for (const p of pages) {
    const errors = [];
    page.on('pageerror', (e) => errors.push(String(e.message || e)));
    try {
      await page.goto(BASE + p, { waitUntil: 'load', timeout: 20000 });
    } catch (e) {
      console.log(`  [${p}] FATAL ${e.message}`);
      continue;
    }
    await page.waitForTimeout(800);
    const info = await page.evaluate(() => {
      const de = document.scrollingElement;
      const ov = de.scrollWidth - de.clientWidth;
      const topbar = document.querySelector('.app-topbar');
      const topbarRect = topbar ? topbar.getBoundingClientRect() : null;
      const menu = document.querySelector('.topbar-menu');
      const menuRect = menu ? menu.getBoundingClientRect() : null;
      // 找出水平溢出的元素
      const offenders = [];
      document.querySelectorAll('body *').forEach(el => {
        const r = el.getBoundingClientRect();
        if (r.right > window.innerWidth + 1.5 && r.width > 0) {
          offenders.push((el.className && typeof el.className === 'string' ? '.' + el.className.split(' ').slice(0,2).join('.') : el.tagName) + ` right=${Math.round(r.right)} w=${Math.round(r.width)}`);
        }
      });
      return {
        ov,
        docW: de.scrollWidth, clientW: de.clientWidth,
        topbarML: topbar ? getComputedStyle(topbar).marginLeft : 'n/a',
        topbarRight: topbarRect ? Math.round(topbarRect.right) : null,
        menuRight: menuRect ? Math.round(menuRect.right) : null,
        innerW: window.innerWidth,
        offenders: offenders.slice(0, 5),
      };
    });
    const flag = info.ov > 1.5 ? ' **OVERFLOW**' : '';
    console.log(`  [${p}] ov=${info.ov} topbarML=${info.topbarML} topbarRight=${info.topbarRight} menuRight=${info.menuRight} winW=${info.innerW}${flag}`);
    if (info.ov > 1.5 && info.offenders.length) {
      console.log('     offenders:', info.offenders.join(' | '));
    }
  }
  await ctx.close();
}

await browser.close();
