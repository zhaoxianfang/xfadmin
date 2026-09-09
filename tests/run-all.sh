#!/usr/bin/env bash
# XfAdmin 包内回归统一入口（零外部依赖，离线可跑）
#
#   bash tests/run-all.sh
#
# 退出码：0 = 全部通过；1 = 任一环节失败。
set -uo pipefail

cd "$(dirname "$0")/.." || exit 1

RC=0
run() {
    local name="$1"; shift
    echo ""
    echo "================ ${name} ================"
    if "$@"; then
        echo "--- ${name}: PASS"
    else
        echo "--- ${name}: FAIL"
        RC=1
    fi
}

PHP_BIN="${PHP_BIN:-php}"

# 全量 PHP 语法检查
SYNTAX_OUT=$(find src -name '*.php' -print0 | xargs -0 -n1 -P8 "${PHP_BIN}" -l 2>&1 | grep -v 'No syntax errors' || true)
echo "================ PHP 语法检查 ================"
if [ -n "${SYNTAX_OUT}" ]; then
    echo "${SYNTAX_OUT}"
    echo "--- PHP 语法检查: FAIL"
    RC=1
else
    echo "--- PHP 语法检查: PASS（$(find src -name '*.php' | wc -l | tr -d ' ') 个文件）"
fi

# JS 语法检查（无 node 时跳过）
if command -v node >/dev/null 2>&1; then
    echo ""
    echo "================ JS 语法检查 ================"
    JS_RC=0
    for f in resources/assets/js/*.js; do
        node --check "$f" || JS_RC=1
    done
    if [ "${JS_RC}" -eq 0 ]; then
        echo "--- JS 语法检查: PASS"
    else
        echo "--- JS 语法检查: FAIL"
        RC=1
    fi
fi

run "静态审计（资源/注入/健壮性/前端契约）" "${PHP_BIN}" -d error_reporting="E_ALL & ~E_DEPRECATED" tests/audit.php
run "冒烟测试"   "${PHP_BIN}" tests/smoke.php
run "回归测试"   "${PHP_BIN}" tests/regression.php

echo ""
if [ "${RC}" -eq 0 ]; then
    echo "ALL GREEN"
else
    echo "FAILED"
fi
exit "${RC}"
