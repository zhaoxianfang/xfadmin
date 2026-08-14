<?php

declare(strict_types=1);

namespace zxf\XfAdmin\Components\Misc;

use zxf\XfAdmin\Components\Component;
use zxf\XfAdmin\XfAdmin;

/**
 * 空闲计时器（misc-idle-timer）—— 用户无操作超时后触发回调（如弹出登录框/提示）
 *
 * XfAdmin::idleTimer([
 *     'timeout'  => 60,           // 秒
 *     'onIdle'   => 'alert("您已离开一会儿")',  // 客户端回调（谨慎使用，建议用 onIdleUrl）
 *     'onIdleUrl'=> '/lock',       // 触发时跳转
 *     'warn'     => 10,            // 提前多少秒提醒（0=不提醒）
 *     'warnText' => '即将因闲置而锁定',
 * ])
 */
class IdleTimer extends Component
{
    /**
     * defaults（protected实例方法）
     *
     * @return array result
     */
    protected function defaults(): array
    {
        return [
            'timeout'   => 60,
            'warn'      => 0,
            'warnText'  => '您已闲置，即将自动锁定',
            'onIdleUrl' => '',
            'onIdle'    => '',
        ];
    }

    /**
     * assets（protected实例方法）
     *
     * @return array result
     */
    protected function assets(): array
    {
        return [];
    }

    /**
     * html（protected实例方法）
     *
     * @return string result
     */
    protected function html(): string
    {
        $id = $this->resolveId('idle');
        $html = '<span' . $this->attrs(['id' => $id, 'style' => 'display:none', 'data-xf' => 'idle-timer'])
            . ' data-xf-config="' . $this->e(json_encode([
                'timeout'  => (int) $this->get('timeout'),
                'warn'     => (int) $this->get('warn'),
                'warnText' => $this->get('warnText'),
                'onIdleUrl'=> $this->get('onIdleUrl'),
                'onIdle'   => $this->get('onIdle'),
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '{}') . '"></span>';

        $js = 'XFAdmin.register("idle-timer",function(el){'
            . 'var c=JSON.parse(el.getAttribute("data-xf-config")||"{}");'
            . 'var t=Math.max(1,c.timeout|0)*1000,warnMs=Math.max(0,c.warn|0)*1000,timer=null,warnTimer=null;'
            . 'function reset(){clearTimeout(timer);clearTimeout(warnTimer);timer=setTimeout(idle,t);'
            // 提前 warn 秒弹出提醒（有任何操作即自动取消）
            . 'if(warnMs>0&&warnMs<t){warnTimer=setTimeout(function(){'
            . 'if(window.XFAdmin&&XFAdmin.toast){XFAdmin.toast({title:"闲置提醒",body:(c.warnText||"您已闲置，即将自动锁定"),variant:"warning",delay:Math.min(warnMs,8000)});}'
            . 'el.dispatchEvent(new CustomEvent("xf.idle.warn"));},t-warnMs);}}'
            . 'function idle(){el.dispatchEvent(new CustomEvent("xf.idle"));'
            . 'if(c.onIdleUrl){location.href=c.onIdleUrl;return;}'
            . 'if(c.onIdle){try{(new Function(c.onIdle))();}catch(e){}}'
            . 'else if(window.XFAdmin&&XFAdmin.toast){XFAdmin.toast({title:"空闲检测",body:"检测到您已闲置 "+(c.timeout|0)+" 秒",variant:"info"});}'
            . 'reset();}'
            . 'window.__xfIdleTimers=window.__xfIdleTimers||[];'
            . 'window.__xfIdleTimers.push({reset:reset});'
            . 'if(!window.__xfIdleGlobalBound){'
            . 'window.__xfIdleGlobalBound=1;'
            . '["mousemove","keydown","click","scroll","touchstart"].forEach(function(ev){'
            . 'document.addEventListener(ev,function(){(window.__xfIdleTimers||[]).forEach(function(o){o.reset();});},true);'
            . '});}'
            . 'reset();'
            . '});';
        XfAdmin::assets()->inlineJs($js, 'xf-idle-timer');

        return $html;
    }
}
