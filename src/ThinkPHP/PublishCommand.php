<?php

declare(strict_types=1);

namespace zxf\XfAdmin\ThinkPHP;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * php think xfadmin:publish
 * 将扩展包静态资源发布到 public/zxf/xfadmin
 */
class PublishCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('xfadmin:publish')->setDescription('发布 XfAdmin 静态资源到 public/zxf/xfadmin');
    }

    protected function execute(Input $input, Output $output): int
    {
        $source = realpath(__DIR__ . '/../../resources/assets');
        $target = $this->app->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'zxf' . DIRECTORY_SEPARATOR . 'xfadmin';

        if ($source === false) {
            $output->error('未找到资源目录');

            return 1;
        }

        $this->copyDir($source, $target);
        $output->info("XfAdmin 资源已发布到: {$target}");

        return 0;
    }

    private function copyDir(string $source, string $target): void
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
                copy($item->getPathname(), $dest);
            }
        }
    }
}
