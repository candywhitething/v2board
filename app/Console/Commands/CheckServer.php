<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramJob;
use App\Models\ServerShadowsocks;
use App\Models\ServerTrojan;
use App\Models\ServerVmess;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;

class CheckServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '节点检查任务';

    /**
     * @var ConsoleOutput
     */
    private $_out;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->_out = new ConsoleOutput();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->checkOffline();
    }

    /**
     * check offline
     *
     * @return void
     */
    private function checkOffline()
    {
        $shadowFaultNodes = ServerShadowsocks::faultNodeNames();
        $vmessFaultNodes = ServerVmess::faultNodeNames();
        $trojanFaultServers = ServerTrojan::faultNodeNames();

        $faultNodes = array_merge($shadowFaultNodes, $vmessFaultNodes, $trojanFaultServers);
        $faultNodesTotal = count($faultNodes);
        $telegramBotEnable = (bool)config('v2board.telegram_bot_enable', 0);
        if ($faultNodesTotal > 0 && $telegramBotEnable) {
            $message = "📮节点检查提醒：\n 现在有{$faultNodesTotal}节点处于离线状态，请立即检查: \n" . join("\n", $faultNodes);
            SendTelegramJob::generateJobWithAdminMessages($message);
        }
        $this->_out->writeln("fault nodes total: " . $faultNodesTotal);
    }
}
