import { spawn, execSync } from 'node:child_process';
import { writeFileSync, rmSync } from 'node:fs';
import path from 'node:path';
const WSF = '/Users/aha/www/wsf';
const PORT = 8091;
const ROUTER = path.join(WSF, '.asset_check_router.php');
const B = String.fromCharCode(92);
const ILL = 'Illuminate' + B + 'Contracts' + B + 'Http' + B + 'Kernel';
const routerPhp = `<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (preg_match('#^/zxf/xfadmin/(.*)$#', $uri, $m)) {
    $file = ${JSON.stringify(WSF + '/vendor/zxf/xfadmin/resources/assets/')} . $m[1];
    if (is_file($file)) { $ext=pathinfo($file,PATHINFO_EXTENSION); $mime=['js'=>'application/javascript','css'=>'text/css','png'=>'image/png','svg'=>'image/svg+xml','jpg'=>'image/jpeg','gif'=>'image/gif','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf'][$ext]??'application/octet-stream'; header('Content-Type: '.$mime); readfile($file); return; }
    http_response_code(404); echo 'NOT_FOUND:' . $file; return;
}
echo 'html';
`;
writeFileSync(ROUTER, routerPhp);
const srv = spawn('php', ['-S', `127.0.0.1:${PORT}`, ROUTER], { cwd: WSF, stdio: 'ignore' });
await new Promise(r => setTimeout(r, 1200));
const assets = ['/zxf/xfadmin/js/xfadmin.js', '/zxf/xfadmin/css/xfadmin.css', '/zxf/xfadmin/plugins/datatables/jquery.dataTables.min.js', '/zxf/xfadmin/js/config.js', '/zxf/xfadmin/plugins/sortable/Sortable.min.js'];
for (const a of assets) {
  try {
    const code = execSync(`curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:${PORT}${a}`).toString().trim();
    console.log(code, a);
  } catch (e) { console.log('ERR', a, e.message); }
}
srv.kill();
rmSync(ROUTER);
