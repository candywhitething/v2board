<?php

namespace App\Utils\Telegram\Commands;

use App\Models\Plan;
use App\Models\User;
use App\Utils\Helper;
use Telegram\Bot\Commands\Command;

class SubLinkCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "subLink";

    /**
     * @var string Command Description
     */
    protected $description = "查看订阅链接";


    /**
     * @inheritdoc
     */
    public function handle()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        /**
         * @var User $user
         */
        $user = User::where(User::FIELD_TELEGRAM_ID, $chatId)->first();
        if ($user === null) {
            $this->triggerCommand('help');
            $this->replyWithMessage([
                'text' => '没有查询到您的用户信息，请先绑定账号',
            ]);
            return;
        }

        /**
         * @var Plan $plan
         */
        $plan = Plan::find($user->getAttribute(User::FIELD_PLAN_ID));
        if ($plan === null) {
            $this->replyWithMessage([
                'text' => '您暂无订阅',
            ]);
            return;
        }

        $subscribe_url = Helper::getSubscribeHost() . "/api/v1/client/subscribe?token={$user['token']}";
        $this->replyWithMessage([
            'text' => "✨我的订阅链接：\n————————————\n$subscribe_url",
            'parse_mode' => 'Markdown'
        ]);
    }
}
