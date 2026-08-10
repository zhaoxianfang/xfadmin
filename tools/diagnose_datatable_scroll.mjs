/**
 * DataTable 滚动和列宽对齐诊断测试
 * 
 * 问题：
 * 1. 浏览器宽度不够时，表格右侧列无法显示，滚动条不正确
 * 2. 表头和表内容宽度严重不一致
 */

import { chromium } from '/Users/aha/www/xfeditor/node_modules/playwright/index.js';

async function diagnose() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  
  // 设置较小的视口宽度来模拟窄屏
  await page.setViewportSize({ width: 1024, height: 768 });
  
  console.log('🔍 开始诊断 DataTable 滚动和列宽问题...\n');
  
  try {
    // 登录
    await page.goto('http://127.0.0.1:8900/admin/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin', { timeout: 10000 });
    console.log('✅ 登录成功\n');
    
    // 进入用户列表页面
    await page.goto('http://127.0.0.1:8900/admin/users', { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000); // 等待 DataTable 初始化
    
    console.log('📊 用户列表页面加载完成\n');
    
    // 1. 检查 DataTable 实例
    const dtInfo = await page.evaluate(() => {
      const table = document.querySelector('.dataTable');
      if (!table) return { error: '未找到 DataTable' };
      
      const dt = $(table).DataTable();
      const settings = dt.settings()[0];
      
      return {
        tableId: table.id,
        scrollX: settings.oInit.scrollX,
        responsive: settings.oInit.responsive,
        columnCount: dt.columns().count(),
        rowCount: dt.rows().count(),
        // 检查 DOM 结构
        hasScrollHead: !!document.querySelector('.dt-scroll-head'),
        hasScrollBody: !!document.querySelector('.dt-scroll-body'),
        hasTableResponsive: !!document.querySelector('.table-responsive'),
      };
    });
    
    console.log('DataTable 配置信息:');
    console.log(JSON.stringify(dtInfo, null, 2));
    console.log('');
    
    // 2. 检查列宽差异
    const columnWidths = await page.evaluate(() => {
      const table = document.querySelector('.dataTable');
      if (!table) return { error: '未找到表格' };
      
      // 检查是否存在 scrollX 结构
      const scrollHead = document.querySelector('.dt-scroll-head table');
      const scrollBody = document.querySelector('.dt-scroll-body table');
      
      if (scrollHead && scrollBody) {
        // scrollX 模式：分别获取表头和表体的列宽
        const headThs = Array.from(scrollHead.querySelectorAll('thead th'));
        const bodyTds = Array.from(scrollBody.querySelectorAll('tbody tr:first-child td'));
        
        const widths = headThs.map((th, i) => {
          const headWidth = th.offsetWidth;
          const bodyWidth = bodyTds[i] ? bodyTds[i].offsetWidth : 0;
          const diff = Math.abs(headWidth - bodyWidth);
          
          return {
            column: th.textContent.trim().substring(0, 20),
            headWidth,
            bodyWidth,
            diff,
            matched: diff <= 1 // 允许 1px 误差
          };
        });
        
        // 获取表格总宽度
        const headTableWidth = scrollHead.offsetWidth;
        const bodyTableWidth = scrollBody.offsetWidth;
        
        return {
          mode: 'scrollX',
          widths,
          headTableWidth,
          bodyTableWidth,
          tableWidthDiff: Math.abs(headTableWidth - bodyTableWidth)
        };
      } else {
        // 非 scrollX 模式
        const ths = Array.from(table.querySelectorAll('thead th'));
        const tds = Array.from(table.querySelectorAll('tbody tr:first-child td'));
        
        const widths = ths.map((th, i) => {
          const headWidth = th.offsetWidth;
          const bodyWidth = tds[i] ? tds[i].offsetWidth : 0;
          const diff = Math.abs(headWidth - bodyWidth);
          
          return {
            column: th.textContent.trim().substring(0, 20),
            headWidth,
            bodyWidth,
            diff,
            matched: diff <= 1
          };
        });
        
        return {
          mode: 'normal',
          widths
        };
      }
    });
    
    console.log('📐 列宽对比分析:');
    if (columnWidths.mode === 'scrollX') {
      console.log(`模式: scrollX (表头和表体分离)`);
      console.log(`表头表格宽度: ${columnWidths.headTableWidth}px`);
      console.log(`表体表格宽度: ${columnWidths.bodyTableWidth}px`);
      console.log(`表格宽度差异: ${columnWidths.tableWidthDiff}px`);
      console.log('');
      
      const mismatched = columnWidths.widths.filter(w => !w.matched);
      if (mismatched.length > 0) {
        console.log(`❌ 发现 ${mismatched.length} 列宽度不匹配:`);
        mismatched.forEach(w => {
          console.log(`  - "${w.column}": 表头 ${w.headWidth}px, 表体 ${w.bodyWidth}px, 差异 ${w.diff}px`);
        });
      } else {
        console.log(`✅ 所有列宽度匹配`);
      }
    } else {
      console.log(`模式: ${columnWidths.mode}`);
    }
    console.log('');
    
    // 3. 检查滚动容器
    const scrollInfo = await page.evaluate(() => {
      const scrollBody = document.querySelector('.dt-scroll-body');
      const tableResponsive = document.querySelector('.table-responsive');
      
      if (scrollBody) {
        return {
          type: 'dt-scroll-body',
          scrollWidth: scrollBody.scrollWidth,
          clientWidth: scrollBody.clientWidth,
          hasHorizontalScroll: scrollBody.scrollWidth > scrollBody.clientWidth,
          scrollLeft: scrollBody.scrollLeft
        };
      } else if (tableResponsive) {
        return {
          type: 'table-responsive',
          scrollWidth: tableResponsive.scrollWidth,
          clientWidth: tableResponsive.clientWidth,
          hasHorizontalScroll: tableResponsive.scrollWidth > tableResponsive.clientWidth,
          scrollLeft: tableResponsive.scrollLeft
        };
      } else {
        return { type: 'none', error: '未找到滚动容器' };
      }
    });
    
    console.log('📜 滚动容器信息:');
    console.log(`容器类型: ${scrollInfo.type}`);
    console.log(`内容宽度: ${scrollInfo.scrollWidth}px`);
    console.log(`可视宽度: ${scrollInfo.clientWidth}px`);
    console.log(`是否有水平滚动: ${scrollInfo.hasHorizontalScroll}`);
    if (scrollInfo.hasHorizontalScroll) {
      console.log(`可滚动距离: ${scrollInfo.scrollWidth - scrollInfo.clientWidth}px`);
    }
    console.log('');
    
    // 4. 尝试滚动测试
    if (scrollInfo.hasHorizontalScroll) {
      console.log('🔄 测试滚动功能...');
      await page.evaluate(() => {
        const scrollBody = document.querySelector('.dt-scroll-body') || document.querySelector('.table-responsive');
        if (scrollBody) {
          // 滚动到最右侧
          scrollBody.scrollLeft = scrollBody.scrollWidth;
        }
      });
      
      await page.waitForTimeout(500);
      
      const afterScroll = await page.evaluate(() => {
        const scrollBody = document.querySelector('.dt-scroll-body') || document.querySelector('.table-responsive');
        return {
          scrollLeft: scrollBody.scrollLeft,
          maxScrollLeft: scrollBody.scrollWidth - scrollBody.clientWidth
        };
      });
      
      console.log(`滚动位置: ${afterScroll.scrollLeft}px / ${afterScroll.maxScrollLeft}px`);
      if (afterScroll.scrollLeft >= afterScroll.maxScrollLeft - 5) {
        console.log(`✅ 可以滚动到最右侧`);
      } else {
        console.log(`❌ 无法滚动到最右侧`);
      }
      console.log('');
    }
    
    // 5. 检查表格总宽度
    const tableSize = await page.evaluate(() => {
      const scrollHead = document.querySelector('.dt-scroll-head table');
      const scrollBody = document.querySelector('.dt-scroll-body table');
      const dataTable = document.querySelector('.dataTable');
      
      const table = scrollHead || scrollBody || dataTable;
      if (!table) return null;
      
      // 获取所有列的宽度总和
      const ths = Array.from(table.querySelectorAll('thead th'));
      const totalColumnWidth = ths.reduce((sum, th) => sum + th.offsetWidth, 0);
      
      // 获取容器宽度
      const container = table.closest('.card-body') || table.closest('.xf-datatable');
      
      return {
        totalColumnWidth,
        tableOffsetWidth: table.offsetWidth,
        containerWidth: container ? container.clientWidth : 0,
        columnCount: ths.length
      };
    });
    
    console.log('📏 表格尺寸信息:');
    console.log(`列数: ${tableSize.columnCount}`);
    console.log(`所有列宽度总和: ${tableSize.totalColumnWidth}px`);
    console.log(`表格实际宽度: ${tableSize.tableOffsetWidth}px`);
    console.log(`容器宽度: ${tableSize.containerWidth}px`);
    console.log(`需要滚动: ${tableSize.totalColumnWidth > tableSize.containerWidth ? '是' : '否'}`);
    console.log('');
    
    // 截图
    await page.screenshot({ path: '/tmp/datatable_diagnose.png', fullPage: true });
    console.log('📸 截图已保存到 /tmp/datatable_diagnose.png\n');
    
  } catch (error) {
    console.error('❌ 诊断失败:', error.message);
    console.error(error.stack);
  } finally {
    await browser.close();
  }
}

diagnose();
