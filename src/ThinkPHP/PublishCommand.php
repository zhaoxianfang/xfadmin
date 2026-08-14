<?php

declare(strict_types=1);

namespace zxf\XfAdmin\ThinkPHP;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

/**
 * php think xfadmin:publish
 * 将扩展包静态资源发布到 public/zxf/xfadmin
 */
class PublishCommand extends Command
{
    /**
     * configure（protected实例方法）
     *
     * @return void result
     */
    protected function configure(): void
    {
        $this->setName('xfadmin:publish')
            ->setDescription('发布 XfAdmin 静态资源到 public/zxf/xfadmin')
            ->addOption('force', 'f', Option::VALUE_NONE, '覆盖已存在的资源文件');
    }

    /**
     * execute（protected实例方法）
     *
     * @param Input $input input
     * @param Output $output output
     *
     * @return int result
     */
    protected function execute(Input $input, Output $output): int
    {
        $source = realpath(__DIR__ . '/../../resources/assets');
        $target = $this->app->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'zxf' . DIRECTORY_SEPARATOR . 'xfadmin';
        $force  = (bool) $input->getOption('force');

        if ($source === false) {
            $output->error('未找到资源目录');

            return 1;
        }
        if (! $force && is_dir($target) && $this->dirHasFiles($target)) {
            $output->warning("目标目录已存在资源: {$target}");
            $output->info('如需覆盖已发布资源，请加 --force 参数：php think xfadmin:publish --force');
            $output->info('（已跳过，未覆盖任何文件）');

            return 0;
        }
        $this->copyDir($source, $target, $force);
        $output->info("XfAdmin 资源已发布到: {$target}" . ($force ? '（已覆盖）' : ''));

        return 0;
    }

    /**
     * copy Dir（private实例方法）
     *
     * @param string $source source
     * @param string $target target
     * @param bool $force force
     *
     * @return void result
     */
    private function copyDir(string $source, string $target, bool $force = false): void
    {
        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $dest = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (! is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
            } else {
                if (! $force && file_exists($dest)) {
                    continue;
                }
                copy($item->getPathname(), $dest);
            }
        }
    }

    /**
     * dir Has Files（private实例方法）
     *
     * @param string $dir dir
     *
     * @return bool result
     */
    private function dirHasFiles(string $dir): bool
    {
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            return true;
        }
        return false;
    }
}
