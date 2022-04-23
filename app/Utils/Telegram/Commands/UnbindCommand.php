<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use Telegram\Bot\Commands\Command;

class UnbindCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "unbind";

    /**
     * @var string Command Description
     */
    protected $description = "解除绑定";


    /**
     * @inheritdoc
     */
    public function handle()
    {
        $chatId = $this->getUpdate()->getChat()->id;
        /**
         * @var User $user
         */
        $user = User::findByTelegramId($chatId);
        if ($user === null) {
            $this->triggerCommand('help');
            $message = '没有查询到您的用户信息，请先绑定账号';
        } else {
            $user->setAttribute(User::FIELD_TELEGRAM_ID, 0);
            if (!$user->save()) {
                abort(500, '解绑失败');
            }
            $message = '解绑成功';
        }

        $this->replyWithMessage(['text' => $message]);
    }
}
