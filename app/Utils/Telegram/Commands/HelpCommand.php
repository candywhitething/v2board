<?php

namespace App\Utils\Telegram\Commands;

use Telegram\Bot\Commands\HelpCommand as BaseHelperCommand;

class HelpCommand extends BaseHelperCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "help";

    /**
     * @var string Command Description
     */
    protected $description = "查看帮助信息";
}
