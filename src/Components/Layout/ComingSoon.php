<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Layout;

use zxf\XfAdmin\XfAdmin;

/**
 * 即将上线页（pages-coming-soon.html）—— 含倒计时
 *
 * echo XfAdmin::comingSoon([
 *     'heading'  => '即将上线',
 *     'message'  => '我们正在努力，敬请期待！',
 *     'deadline' => '2026-12-31 00:00:00',
 *     'image'    => null,
 *     'subscribe'=> true,          // 显示订阅表单
 * ]);
 */
class ComingSoon extends AuthPage
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return array_replace(parent::defaults(), [
            'heading' => '即将上线',
            'message' => '我们正在努力打造精彩内容，敬请期待。',
            'deadline' => null,
            'image' => null,
            'subscribe' => true,
            'subscribe_url' => null,   // 订阅接收地址；留空时仅展示禁用控件，不做假提交
        ]);
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->uid('countdown');

        $content = '<div class="text-center">';
        if ($this->get('image')) {
            $content .= '<img src="'.$this->escape(XfAdmin::img((string) $this->get('image'))).'" class="img-fluid mb-3" alt="">';
        }
        $content .= '<h2 class="fw-bold">'.$this->escape($this->get('heading')).'</h2>';
        $content .= '<p class="text-muted">'.$this->escape($this->get('message')).'</p>';

        if ($this->get('deadline')) {
            // strtotime 解析失败返回 false，直接乘 1000 会得到 0（1970 起点）导致倒计时异常；
            // 解析失败时按 0 处理并加注释说明：前端 data-deadline="0" 表示无有效截止时间
            $ts = strtotime((string) $this->get('deadline'));
            $ms = $ts === false ? 0 : $ts * 1000;
            $content .= '<div class="d-flex justify-content-center gap-3 my-4" data-xf="countdown" data-deadline="'.$ms.'" id="'.$id.'">';
            foreach (['days' => '天', 'hours' => '时', 'minutes' => '分', 'seconds' => '秒'] as $unit => $label) {
                $content .= '<div class="card mb-0"><div class="card-body px-3 py-2 text-center">'
                    .'<h2 class="mb-0" data-unit="'.$unit.'">00</h2><small class="text-muted">'.$label.'</small>'
                    .'</div></div>';
            }
            $content .= '</div>';
        }
        if ($this->get('subscribe')) {
            // 只有配置了合法接收地址才渲染可提交表单（name/action/method 齐全）；
            // 否则纯展示 + 禁用控件，杜绝无 action、无 name、无 JS 绑定的死表单。
            $subscribeUrl = $this->safeUrl($this->scalarOpt('subscribe_url'));
            if ($subscribeUrl !== '' && $subscribeUrl !== '#') {
                $content .= '<form class="d-flex gap-2 justify-content-center mt-3" style="max-width:420px;margin:0 auto;"'
                    .' action="'.$this->escape($subscribeUrl).'" method="post">'
                    .'<input type="email" name="email" class="form-control" placeholder="输入邮箱订阅上线通知" required>'
                    .'<button class="btn btn-primary" type="submit">订阅</button></form>';
            } else {
                $content .= '<div class="d-flex gap-2 justify-content-center mt-3" style="max-width:420px;margin:0 auto;">'
                    .'<input type="email" class="form-control" placeholder="订阅功能暂未开放" disabled>'
                    .'<button class="btn btn-primary" type="button" disabled>订阅</button></div>'
                    .'<p class="text-muted small mt-2 mb-0">订阅功能暂未开放，请稍后再试。</p>';
            }
        }
        $content .= '</div>';

        $this->set('content', $content);
        if (! $this->get('title')) {
            $this->set('title', $this->get('heading'));
        }

        return parent::html();
    }
}
