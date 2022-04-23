<?php

namespace App\Utils\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class LatestURLCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "latestURL";

    /**
     * @var string Command Description
     */
    protected $description = "获取最新的网址";


    /**
     * @inheritdoc
     */
    public function handle()
    {
        $text = sprintf(
            "%s的最新网址是：%s",
            config('v2board.app_name', 'V2Board'),
            config('v2board.app_url')
        );

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
