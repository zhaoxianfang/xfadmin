/**
 * 简化版 ApexCharts resize 测试
 */
import pkg from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
const { chromium } = pkg;

async function testApexResize() {
    console.log('🚀 启动测试...');
    
    const browser = await chromium.launch({ headless: true });
    console.log('✅ 浏览器已启动');
    
    const page = await browser.newPage();
    console.log('✅ 页面已创建');
    
    try {
        // 拦截外部请求
        await page.route('**', (route) => {
            const url = route.request().url();
            if (url.startsWith('http://127.0.0.1:8900') || url.startsWith('data:')) {
                route.continue();
            } else {
                route.abort();
            }
        });
        console.log('✅ 路由拦截已设置');
        
        // 访问页面
        console.log('📊 访问 /charts 页面...');
        await page.goto('http://127.0.0.1:8900/charts', { waitUntil: 'networkidle', timeout: 30000 });
        console.log('✅ 页面已加载');
        
        await page.waitForTimeout(1000);
        
        // 测试初始状态
        const initialOverflow = await page.evaluate(() => {
            return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
        });
        console.log(`📏 初始溢出: ${initialOverflow}px`);
        
        // 测试调整宽度
        const testWidths = [1920, 1280, 768, 375];
        let maxOverflow = 0;
        
        for (const width of testWidths) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(500);
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            console.log(`📏 宽度 ${width}px: 溢出 ${overflow}px`);
            
            if (overflow > maxOverflow) {
                maxOverflow = overflow;
            }
        }
        
        console.log(`\n最大溢出: ${maxOverflow}px`);
        
        if (maxOverflow <= 10) {
            console.log('✅ 测试通过: 所有宽度下溢出 <= 10px');
            await browser.close();
            process.exit(0);
        } else {
            console.log(`❌ 测试失败: 最大溢出 ${maxOverflow}px > 10px`);
            await browser.close();
            process.exit(1);
        }
        
    } catch (error) {
        console.error('❌ 测试失败:', error.message);
        await browser.close();
        process.exit(1);
    }
}

testApexResize().catch((error) => {
    console.error('❌ 未捕获的错误:', error);
    process.exit(1);
});
