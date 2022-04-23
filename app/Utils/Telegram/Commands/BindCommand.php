<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use Telegram\Bot\Commands\Command;

class BindCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "bind";

    /**
     * @var string Command Description
     */
    protected $description = "订阅地址绑定";

    /**
     * @var string   Command Argument Pattern
     */
    protected $pattern = '.*+';

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $subscribeURL = $this->arguments['custom'] ?? '';

        if (empty($subscribeURL)) {
            $this->replyWithMessage(['text' => '参数有误，请携带订阅地址发送']);
            return;
        }

        $url = parse_url($subscribeURL);
        if (empty($url['query'])) {
            $this->replyWithMessage(['text' => '订阅地址无效']);
            return;
        }

        parse_str($url['query'], $query);
        $token = $query['token'] ?? null;
        if (!$token) {
            $this->replyWithMessage(['text' => '订阅地址无效']);
            return;
        }

        /**
         * @var User $user
         */
        $user = User::findByToken($token);
        if ($user === null) {
            $this->replyWithMessage(['text' => '用户不存在']);
            return;
        }

        if ($user->getAttribute(User::FIELD_TELEGRAM_ID)) {
            $this->replyWithMessage(['text' => '该账号已经绑定了Telegram账号']);
            return;
        }

        $user->setAttribute(User::FIELD_TELEGRAM_ID, $this->getUpdate()->getChat()->id);
        if (!$user->save()) {
            $this->replyWithMessage(['text' => '设置失败']);
            return;
        }

        $this->replyWithMessage(['text' => '绑定成功']);
    }
}
