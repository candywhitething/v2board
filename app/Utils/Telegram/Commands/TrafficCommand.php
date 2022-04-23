<?php

namespace App\Utils\Telegram\Commands;

use App\Models\Plan;
use App\Models\User;
use App\Utils\Helper;
use Telegram\Bot\Commands\Command;

class TrafficCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "traffic";

    /**
     * @var string Command Description
     */
    protected $description = "查询流量信息";


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
            $message = '没有查询到您的用户信息，请先绑定账号';
        } else {
            /**
             * @var Plan $plan
             */
            $plan = $user->plan();
            if ($plan === null || $user->isExpired()) {
                $message = '抱歉，未能查到您的有效订阅，请登录网站查询您的账号状态';
            } else {
                $transferEnableValue = Helper::trafficConvert($plan->getAttribute(Plan::FIELD_TRANSFER_ENABLE_VALUE));
                $up = Helper::trafficConvert($user->getAttribute(User::FIELD_U));
                $down = Helper::trafficConvert($user->getAttribute(User::FIELD_D));
                $remaining = Helper::trafficConvert($plan->getAttribute(Plan::FIELD_TRANSFER_ENABLE_VALUE) - ($user->getAttribute(User::FIELD_U) + $user->getAttribute(User::FIELD_D)));
                $message = "🚥流量查询\n———————————————\n计划流量：`$transferEnableValue`\n已用上行：`$up`\n已用下行：`$down`\n剩余流量：`$remaining`";
            }
        }

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}
