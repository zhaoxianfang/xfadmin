<?php

declare(strict_types=1);

use zxf\XfAdmin\XfAdmin;

http_response_code(404);

echo XfAdmin::errorPage([
    'code'     => 404,
    'heading'  => '页面不存在',
    'message'  => '您访问的页面不存在或已被移动。',
    'home_url' => '/',
]);
