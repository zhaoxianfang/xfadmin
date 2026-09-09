#!/usr/bin/env bash
# Demo 端到端冒烟：起 php -S 渲染 demo 全部页面，校验
#   1) 每个页面 HTTP 200 且响应体 / 服务日志无 PHP 错误
#   2) 页面引用的本地资源（/zxf/xfadmin/*）全部可访问（无 404）
#
#   bash tests/demo_smoke.sh
#
# 退出码：0 = 全部通过；1 = 存在失败。无 curl 时自动跳过（退出 0）。
set -uo pipefail

cd "$(dirname "$0")/.." || exit 1

PORT="${PORT:-8907}"
PHP_BIN="${PHP_BIN:-php}"

if ! command -v curl >/dev/null 2>&1; then
    echo "demo 冒烟：未找到 curl，跳过"
    exit 0
fi

LOG=$(mktemp -t xf_demo_smoke)
"${PHP_BIN}" -S "127.0.0.1:${PORT}" demo/router.php >"${LOG}" 2>&1 &
SRV=$!
cleanup() { kill "${SRV}" 2>/dev/null; wait "${SRV}" 2>/dev/null; rm -f "${LOG}"; }
trap cleanup EXIT

# 等待服务就绪
for _ in $(seq 1 30); do
    if curl -s -o /dev/null --max-time 2 "http://127.0.0.1:${PORT}/" 2>/dev/null; then break; fi
    sleep 0.3
done

FAIL=0
PAGES="/ widgets apps landing topnav tables forms charts login auth 404"

echo "================ Demo 端到端冒烟（端口 ${PORT}） ================"
for p in ${PAGES}; do
    url="http://127.0.0.1:${PORT}/${p}"
    body=$(curl -s --max-time 20 "${url}")
    code=$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "${url}")
    err=$(printf '%s' "${body}" | grep -oE '(Fatal error|Parse error|Uncaught [A-Za-z\\]+|Warning:|Notice:|Deprecated:)' | head -1)
    if [ "${code}" != "200" ] || [ -n "${err}" ]; then
        echo "  ✘ /${p}  code=${code} ${err}"
        FAIL=1
    else
        echo "  ✔ /${p}  200 ($(printf '%s' "${body}" | wc -c | tr -d ' ') bytes)"
    fi
done

echo "----------------------------------------------------------------"
echo "本地资源可达性检查"
for p in widgets charts tables forms; do
    body=$(curl -s --max-time 20 "http://127.0.0.1:${PORT}/${p}")
    assets=$(printf '%s' "${body}" | grep -oE '/zxf/xfadmin/[A-Za-z0-9_./-]+' | sort -u)
    missing=""
    for a in ${assets}; do
        ac=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "http://127.0.0.1:${PORT}${a}")
        if [ "${ac}" != "200" ]; then missing="${missing} ${a}(${ac})"; fi
    done
    total=$(printf '%s\n' "${assets}" | grep -c . || true)
    if [ -n "${missing}" ]; then
        echo "  ✘ /${p} 引用资源 ${total} 个，缺失:${missing}"
        FAIL=1
    else
        echo "  ✔ /${p} 引用资源 ${total} 个全部 200"
    fi
done

if [ "${FAIL}" -eq 0 ]; then
    echo "--- Demo 冒烟: PASS"
else
    echo "--- Demo 冒烟: FAIL"
fi
exit "${FAIL}"
