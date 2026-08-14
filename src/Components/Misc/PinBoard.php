<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 便利贴看板（pin-board.html）
 *
 * XfAdmin::pinBoard([
 *     'notes' => [
 *         ['color'=>'warning','title'=>'设计评审','text'=>'周五前确认首页视觉','author'=>'张三','time'=>'10:30'],
 *         ['color'=>'info','title'=>'Bug 修复','text'=>'登录页 500','author'=>'李四','time'=>'昨天'],
 *     ],
 * ])
 */
class PinBoard extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return ['notes' => [], 'addable' => true];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $notes = (array) $this->get('notes', []);
        if (empty($notes)) {
            return '';
        }
        $html = '<div class="pin-board">';
        foreach ($notes as $n) {
            $n     = (array) $n;
            $color = $this->enum($n['color'] ?? 'warning', self::ENUM_VARIANT, 'warning');
            $html .= '<div class="pin-note pin-' . $color . '">';
            $html .= '<div class="pin-note-head"><i class="ti ti-pin"></i>';
            if (! empty($n['title'])) {
                $html .= '<span class="fw-semibold">' . $this->e($n['title']) . '</span>';
            }
            $html .= '<button class="btn btn-sm btn-icon ms-auto text-dark"><i class="ti ti-x"></i></button></div>';
            $html .= '<div class="pin-note-body">' . nl2br($this->e($n['text'] ?? '')) . '</div>';
            if (! empty($n['author']) || ! empty($n['time'])) {
                $html .= '<div class="pin-note-foot"><small class="text-muted">' . $this->e($n['author'] ?? '') . ' · ' . $this->e($n['time'] ?? '') . '</small></div>';
            }
            $html .= '</div>';
        }
        if ($this->get('addable')) {
            $html .= '<div class="pin-note pin-add"><button class="btn btn-light w-100 h-100"><i class="ti ti-plus"></i> 添加便利贴</button></div>';
        }
        return $html . '</div>';
    }
}
