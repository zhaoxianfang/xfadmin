#!/usr/bin/env bash
# 一键运行包内浏览器级自测：
#   build.php 渲染全部组件 → php -S 起服务 → selftest.mjs (Playwright) 检查
# 用法：bash tools/selftest/run.sh
set -euo pipefail

cd "$(dirname "$0")/../.."          # tools/selftest → 仓库根
PORT="${PORT:-8919}"

echo "== 1/4 build =="
php tools/selftest/build.php

echo "== 2/4 xss audit =="
php tools/selftest/xss_audit.php

echo "== 3/4 asset check =="
php tools/selftest/asset_check.php

echo "== 4/4 start server =="
php -S "127.0.0.1:${PORT}" tools/selftest/router.php >/tmp/xfadmin_selftest_srv.log 2>&1 &
SRV=$!
trap 'kill "$SRV" 2>/dev/null || true' EXIT
sleep 1

echo "== playwright =="
SELFTEST_BASE="http://127.0.0.1:${PORT}" node tools/selftest/selftest.mjs
RC=$?
exit "$RC"
