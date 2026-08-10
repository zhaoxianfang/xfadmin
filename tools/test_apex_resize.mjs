/**
 * 测试 ApexCharts resize 修复
 * 验证调整浏览器宽度时图表是否正确适应容器，无横向溢出
 */
import pkg from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
const { chromium } = pkg;

async function testApexResize() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    const results = {
        passed: [],
        failed: [],
        errors: []
    };
    
    try {
        // 拦截外部请求（避免 OSM 瓦片等外部资源干扰）
        await page.route('**', (route) => {
            const url = route.request().url();
            if (url.startsWith('http://127.0.0.1:8900') || url.startsWith('data:')) {
                route.continue();
            } else {
                route.abort();
            }
        });
        
        // 测试 /charts 页面（包含多个 ApexCharts）
        console.log('📊 测试 /charts 页面...');
        await page.goto('http://127.0.0.1:8900/charts', { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(1000);
        
        // 检查初始状态
        const initialOverflow = await page.evaluate(() => {
            return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
        });
        
        if (initialOverflow > 0) {
            results.failed.push({
                test: '初始状态无溢出',
                expected: 0,
                actual: initialOverflow,
                page: '/charts'
            });
        } else {
            results.passed.push({
                test: '初始状态无溢出',
                page: '/charts'
            });
        }
        
        // 测试调整宽度序列（从大到小）
        const widths = [1920, 1440, 1280, 1024, 768, 576, 375, 320];
        
        for (const width of widths) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(500); // 等待图表 resize
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            if (overflow > 10) { // 允许 10px 误差
                results.failed.push({
                    test: `宽度 ${width}px 无溢出`,
                    expected: '<=10px',
                    actual: `${overflow}px`,
                    page: '/charts'
                });
            } else {
                results.passed.push({
                    test: `宽度 ${width}px 无溢出`,
                    page: '/charts'
                });
            }
        }
        
        // 测试调整宽度序列（从小到大）
        const reverseWidths = [320, 375, 576, 768, 1024, 1280, 1440, 1920];
        
        for (const width of reverseWidths) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(500);
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            if (overflow > 10) {
                results.failed.push({
                    test: `宽度 ${width}px 无溢出（反向）`,
                    expected: '<=10px',
                    actual: `${overflow}px`,
                    page: '/charts'
                });
            } else {
                results.passed.push({
                    test: `宽度 ${width}px 无溢出（反向）`,
                    page: '/charts'
                });
            }
        }
        
        // 检查是否有 JS 错误
        const jsErrors = [];
        page.on('pageerror', (error) => {
            jsErrors.push(error.message);
        });
        
        // 重新加载页面，测试 resize 事件绑定
        await page.goto('http://127.0.0.1:8900/charts', { waitUntil: 'networkidle' });
        await page.waitForTimeout(1000);
        
        // 快速调整宽度（测试防抖）
        for (let i = 0; i < 10; i++) {
            await page.setViewportSize({ width: 800 + i * 100, height: 900 });
            await page.waitForTimeout(50);
        }
        
        await page.waitForTimeout(500);
        
        if (jsErrors.length > 0) {
            results.errors.push({
                test: '无 JS 错误',
                errors: jsErrors
            });
        } else {
            results.passed.push({
                test: '无 JS 错误',
                page: '/charts'
            });
        }
        
    } catch (error) {
        results.errors.push({
            test: '测试执行',
            error: error.message
        });
    } finally {
        await browser.close();
    }
    
    // 输出结果
    console.log('\n' + '='.repeat(60));
    console.log('ApexCharts Resize 测试结果');
    console.log('='.repeat(60));
    console.log(`✅ 通过: ${results.passed.length}`);
    console.log(`❌ 失败: ${results.failed.length}`);
    console.log(`⚠️  错误: ${results.errors.length}`);
    
    if (results.failed.length > 0) {
        console.log('\n失败项:');
        results.failed.forEach((item, i) => {
            console.log(`  ${i + 1}. ${item.test} - 预期: ${item.expected}, 实际: ${item.actual}`);
        });
    }
    
    if (results.errors.length > 0) {
        console.log('\n错误项:');
        results.errors.forEach((item, i) => {
            console.log(`  ${i + 1}. ${item.test}: ${item.error || item.errors?.join(', ')}`);
        });
    }
    
    const success = results.failed.length === 0 && results.errors.length === 0;
    console.log('\n' + '='.repeat(60));
    console.log(success ? '✅ 测试通过' : '❌ 测试失败');
    console.log('='.repeat(60));
    
    process.exit(success ? 0 : 1);
}

testApexResize().catch((error) => {
    console.error('❌ 测试执行失败:', error);
    process.exit(1);
});
