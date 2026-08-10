/**
 * 测试 DataTable 滚动和样式问题
 * 1. 水平滚动条
 * 2. 列宽对齐
 * 3. 双滚动条
 * 4. 页面标题样式
 */
import pkg from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';
const { chromium } = pkg;

async function testDataTableScroll() {
    console.log('🚀 启动 DataTable 滚动测试...\n');
    
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    const results = {
        issues: [],
        passed: []
    };
    
    try {
        // 拦截外部请求
        await page.route('**', (route) => {
            const url = route.request().url();
            if (url.startsWith('http://127.0.0.1') || url.startsWith('data:')) {
                route.continue();
            } else {
                route.abort();
            }
        });
        
        // 访问用户列表页面（需要登录）
        console.log('📊 访问用户列表页面...');
        
        // 先访问登录页面
        await page.goto('http://127.0.0.1:8000/admin/login', { 
            waitUntil: 'networkidle', 
            timeout: 30000 
        });
        await page.waitForTimeout(1000);
        
        // 检查是否已经在登录页面
        const hasLoginForm = await page.evaluate(() => {
            return document.querySelector('input[type="password"]') !== null;
        });
        
        if (hasLoginForm) {
            console.log('  需要登录...');
            
            // 填写登录表单（使用演示账号）
            await page.fill('input[name="username"]', 'admin');
            await page.fill('input[name="password"]', 'admin123');
            await page.click('button[type="submit"]');
            
            // 等待登录完成
            await page.waitForTimeout(2000);
        }
        
        // 访问用户列表页面
        await page.goto('http://127.0.0.1:8000/admin/users', { 
            waitUntil: 'networkidle', 
            timeout: 30000 
        });
        await page.waitForTimeout(3000); // 增加等待时间，确保DataTable完全初始化
        
        // 检查页面标题样式
        console.log('\n📋 检查页面标题样式...');
        const pageTitle = await page.evaluate(() => {
            const el = document.querySelector('.page-title-head');
            if (!el) return null;
            
            const styles = window.getComputedStyle(el);
            return {
                padding: styles.padding,
                margin: styles.margin,
                height: el.offsetHeight,
                content: el.textContent.trim().substring(0, 50)
            };
        });
        
        if (pageTitle) {
            console.log(`  页面标题: "${pageTitle.content}"`);
            console.log(`  padding: ${pageTitle.padding}`);
            console.log(`  margin: ${pageTitle.margin}`);
            console.log(`  height: ${pageTitle.height}px`);
            
            // 检查padding是否合理
            const paddingValues = pageTitle.padding.split(' ').map(v => parseFloat(v));
            const topPadding = paddingValues[0] || 0;
            const bottomPadding = paddingValues[2] || paddingValues[0] || 0;
            
            // padding 应该在 1rem-2rem 之间（16px-32px）
            if (topPadding < 16 || topPadding > 32 || bottomPadding < 16 || bottomPadding > 32) {
                results.issues.push({
                    type: 'page-title-padding',
                    message: `页面标题padding不合理: ${pageTitle.padding}`,
                    expected: '16px-32px (1rem-2rem)',
                    actual: pageTitle.padding
                });
            } else {
                results.passed.push('页面标题padding合理');
            }
        }
        
        // 检查表格容器
        console.log('\n📋 检查表格容器...');
        const tableInfo = await page.evaluate(() => {
            const table = document.querySelector('.xf-datatable');
            if (!table) return null;
            
            const wrapper = table.closest('.dataTables_wrapper') || table.closest('.dt-container');
            const scrollBody = document.querySelector('.dataTables_scrollBody') || 
                              document.querySelector('.dt-scroll-body');
            const scrollHead = document.querySelector('.dataTables_scrollHead') || 
                              document.querySelector('.dt-scroll-head');
            
            // 检查水平滚动条
            const hasHorizontalScroll = table.scrollWidth > table.clientWidth;
            const scrollBarCount = document.querySelectorAll('[style*="overflow"], .dataTables_scroll, .dt-scroll').length;
            
            // 检查列宽对齐
            const headers = Array.from(table.querySelectorAll('thead th'));
            const firstRow = table.querySelector('tbody tr:first-child');
            const cells = firstRow ? Array.from(firstRow.querySelectorAll('td')) : [];
            
            const columnWidths = headers.map((th, i) => {
                const headerWidth = th.offsetWidth;
                const cellWidth = cells[i] ? cells[i].offsetWidth : 0;
                const diff = Math.abs(headerWidth - cellWidth);
                return { index: i, headerWidth, cellWidth, diff };
            });
            
            const misalignedColumns = columnWidths.filter(c => c.diff > 2);
            
            return {
                tableWidth: table.offsetWidth,
                tableScrollWidth: table.scrollWidth,
                hasHorizontalScroll,
                scrollBarCount,
                wrapperClass: wrapper?.className || 'none',
                scrollBodyClass: scrollBody?.className || 'none',
                scrollHeadClass: scrollHead?.className || 'none',
                columnCount: headers.length,
                misalignedColumns: misalignedColumns.length,
                columnWidths: columnWidths.slice(0, 5) // 只显示前5列
            };
        });
        
        if (tableInfo) {
            console.log(`  表格宽度: ${tableInfo.tableWidth}px`);
            console.log(`  表格滚动宽度: ${tableInfo.tableScrollWidth}px`);
            console.log(`  有水平滚动: ${tableInfo.hasHorizontalScroll}`);
            console.log(`  滚动容器数量: ${tableInfo.scrollBarCount}`);
            console.log(`  列数: ${tableInfo.columnCount}`);
            console.log(`  未对齐列数: ${tableInfo.misalignedColumns}`);
            console.log(`  容器类: ${tableInfo.wrapperClass}`);
            console.log(`  滚动体类: ${tableInfo.scrollBodyClass}`);
            console.log(`  前5列宽度:`, tableInfo.columnWidths);
            
            // 检查水平滚动条
            if (!tableInfo.hasHorizontalScroll && tableInfo.tableScrollWidth > tableInfo.tableWidth) {
                results.issues.push({
                    type: 'no-horizontal-scroll',
                    message: '表格内容超出容器但没有水平滚动条',
                    expected: '有滚动条',
                    actual: '无滚动条'
                });
            } else if (tableInfo.hasHorizontalScroll) {
                results.passed.push('表格有水平滚动条');
            }
            
            // 检查双滚动条
            if (tableInfo.scrollBarCount > 1) {
                results.issues.push({
                    type: 'double-scrollbar',
                    message: `发现${tableInfo.scrollBarCount}个滚动容器，可能存在双滚动条`,
                    expected: '1个滚动容器',
                    actual: `${tableInfo.scrollBarCount}个`
                });
            } else {
                results.passed.push('滚动容器数量正常');
            }
            
            // 检查列宽对齐
            if (tableInfo.misalignedColumns > 0) {
                results.issues.push({
                    type: 'column-misalignment',
                    message: `${tableInfo.misalignedColumns}列的表头和内容宽度不匹配`,
                    expected: '所有列对齐',
                    actual: `${tableInfo.misalignedColumns}列未对齐`
                });
            } else {
                results.passed.push('所有列宽对齐');
            }
        }
        
        // 检查表格样式
        console.log('\n📋 检查表格样式...');
        const styles = await page.evaluate(() => {
            const table = document.querySelector('.xf-datatable');
            if (!table) return null;
            
            const computedStyle = window.getComputedStyle(table);
            const thead = table.querySelector('thead');
            const theadStyle = thead ? window.getComputedStyle(thead) : null;
            
            return {
                tableBorder: computedStyle.border,
                tableBorderCollapse: computedStyle.borderCollapse,
                theadBg: theadStyle?.background || 'none',
                theadColor: theadStyle?.color || 'none',
                stripedClass: table.classList.contains('table-striped'),
                hoverClass: table.classList.contains('table-hover')
            };
        });
        
        if (styles) {
            console.log(`  表格边框: ${styles.tableBorder}`);
            console.log(`  边框合并: ${styles.tableBorderCollapse}`);
            console.log(`  表头背景: ${styles.theadBg}`);
            console.log(`  表头颜色: ${styles.theadColor}`);
            console.log(`  斑马纹: ${styles.stripedClass}`);
            console.log(`  悬停效果: ${styles.hoverClass}`);
        }
        
        // 截图
        await page.screenshot({ 
            path: '/tmp/datatable_scroll_test.png', 
            fullPage: true 
        });
        console.log('\n📸 截图已保存: /tmp/datatable_scroll_test.png');
        
    } catch (error) {
        console.error('❌ 测试失败:', error.message);
        results.issues.push({
            type: 'test-error',
            message: error.message
        });
    } finally {
        await browser.close();
    }
    
    // 输出结果
    console.log('\n' + '='.repeat(60));
    console.log('测试结果');
    console.log('='.repeat(60));
    console.log(`✅ 通过: ${results.passed.length}`);
    console.log(`❌ 问题: ${results.issues.length}`);
    
    if (results.issues.length > 0) {
        console.log('\n发现的问题:');
        results.issues.forEach((issue, i) => {
            console.log(`  ${i + 1}. [${issue.type}] ${issue.message}`);
            if (issue.expected) {
                console.log(`     预期: ${issue.expected}`);
                console.log(`     实际: ${issue.actual}`);
            }
        });
    }
    
    if (results.passed.length > 0) {
        console.log('\n通过的检查:');
        results.passed.forEach((item, i) => {
            console.log(`  ${i + 1}. ${item}`);
        });
    }
    
    const success = results.issues.length === 0;
    console.log('\n' + '='.repeat(60));
    console.log(success ? '✅ 所有测试通过' : '❌ 发现问题需要修复');
    console.log('='.repeat(60));
    
    process.exit(success ? 0 : 1);
}

testDataTableScroll().catch((error) => {
    console.error('❌ 未捕获的错误:', error);
    process.exit(1);
});
