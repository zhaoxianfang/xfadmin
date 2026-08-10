/**
 * ApexCharts resize 压力测试
 * 测试快速连续调整宽度、防抖功能、双向调整
 */
import pkg from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
const { chromium } = pkg;

async function testApexResizeStress() {
    console.log('🚀 启动压力测试...\n');
    
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    const results = {
        static: [],
        resize_sequence: [],
        rapid_resize: [],
        errors: []
    };
    
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
        
        // 监听 JS 错误
        page.on('pageerror', (error) => {
            results.errors.push(error.message);
        });
        
        // 访问页面
        console.log('📊 访问 /charts 页面...');
        await page.goto('http://127.0.0.1:8900/charts', { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(1000);
        
        // 测试 1: 静态页面加载（各宽度）
        console.log('\n📋 测试 1: 静态页面加载');
        const staticWidths = [1920, 1440, 1280, 1024, 768, 576, 375, 320];
        
        for (const width of staticWidths) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(300);
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            results.static.push({ width, overflow });
            const status = overflow <= 10 ? '✅' : '❌';
            console.log(`  ${status} ${width}px: ${overflow}px`);
        }
        
        // 测试 2: 调整宽度序列（从大到小）
        console.log('\n📋 测试 2: 调整宽度序列（大→小）');
        for (const width of [1920, 1440, 1280, 1024, 768, 576, 375, 320]) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(500); // 等待 resize 事件
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            results.resize_sequence.push({ direction: 'down', width, overflow });
            const status = overflow <= 10 ? '✅' : '❌';
            console.log(`  ${status} ${width}px: ${overflow}px`);
        }
        
        // 测试 3: 调整宽度序列（从小到大）
        console.log('\n📋 测试 3: 调整宽度序列（小→大）');
        for (const width of [320, 375, 576, 768, 1024, 1280, 1440, 1920]) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(500);
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            results.resize_sequence.push({ direction: 'up', width, overflow });
            const status = overflow <= 10 ? '✅' : '❌';
            console.log(`  ${status} ${width}px: ${overflow}px`);
        }
        
        // 测试 4: 快速连续调整宽度（测试防抖）
        console.log('\n📋 测试 4: 快速连续调整宽度（防抖测试）');
        const rapidChanges = [];
        for (let i = 0; i < 20; i++) {
            const width = 600 + Math.random() * 800; // 随机宽度
            await page.setViewportSize({ width: Math.floor(width), height: 900 });
            await page.waitForTimeout(30); // 快速调整，不等待 resize 完成
            
            const overflow = await page.evaluate(() => {
                return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
            });
            
            rapidChanges.push(overflow);
        }
        
        // 等待防抖完成
        await page.waitForTimeout(500);
        
        const finalOverflow = await page.evaluate(() => {
            return document.scrollingElement.scrollWidth - document.scrollingElement.clientWidth;
        });
        
        results.rapid_resize = rapidChanges;
        const maxRapidOverflow = Math.max(...rapidChanges);
        const status = finalOverflow <= 10 ? '✅' : '❌';
        console.log(`  ${status} 快速调整后最终溢出: ${finalOverflow}px (过程中最大: ${maxRapidOverflow}px)`);
        
        // 测试 5: 检查 JS 错误
        console.log('\n📋 测试 5: JS 错误检查');
        if (results.errors.length === 0) {
            console.log('  ✅ 无 JS 错误');
        } else {
            console.log(`  ❌ 发现 ${results.errors.length} 个 JS 错误:`);
            results.errors.forEach((err, i) => {
                console.log(`    ${i + 1}. ${err}`);
            });
        }
        
        // 统计结果
        console.log('\n' + '='.repeat(60));
        console.log('测试结果统计');
        console.log('='.repeat(60));
        
        const staticPass = results.static.every(r => r.overflow <= 10);
        const sequencePass = results.resize_sequence.every(r => r.overflow <= 10);
        const rapidPass = finalOverflow <= 10;
        const noErrors = results.errors.length === 0;
        
        console.log(`静态加载: ${staticPass ? '✅ 通过' : '❌ 失败'}`);
        console.log(`调整序列: ${sequencePass ? '✅ 通过' : '❌ 失败'}`);
        console.log(`快速调整: ${rapidPass ? '✅ 通过' : '❌ 失败'}`);
        console.log(`JS 错误: ${noErrors ? '✅ 无错误' : '❌ 有错误'}`);
        
        const allPass = staticPass && sequencePass && rapidPass && noErrors;
        console.log('\n' + '='.repeat(60));
        console.log(allPass ? '✅ 所有测试通过' : '❌ 部分测试失败');
        console.log('='.repeat(60));
        
        await browser.close();
        process.exit(allPass ? 0 : 1);
        
    } catch (error) {
        console.error('❌ 测试失败:', error.message);
        await browser.close();
        process.exit(1);
    }
}

testApexResizeStress().catch((error) => {
    console.error('❌ 未捕获的错误:', error);
    process.exit(1);
});
