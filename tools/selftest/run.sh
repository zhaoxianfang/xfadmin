#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

# 1) 构建全部组件静态页
php build.php

# 1.5) XSS 模糊审计（文本字段转义一致性）
php xss_audit.php

# 1.6) 资源依赖完整性校验
php asset_check.php

# 2) 启动本地服务并运行 Playwright 自测
php -S 127.0.0.1:8903 router.php >/tmp/xfadmin_selftest_php.log 2>&1 &
PID=$!
trap 'kill $PID 2>/dev/null || true' EXIT

# 等待服务就绪
for i in $(seq 1 30); do
    if curl -s -o /dev/null "http://127.0.0.1:8903/doc_index.json" 2>/dev/null; then
        break
    fi
    sleep 0.3
done

BASE="http://127.0.0.1:8903" node selftest.mjs
