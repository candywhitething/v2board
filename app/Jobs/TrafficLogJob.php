<?php

namespace App\Jobs;

use App\Models\TrafficServerLog;
use App\Models\TrafficUserLog;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class TrafficLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $u;
    protected $d;
    protected $userId;
    protected $serverId;
    protected $serverType;
    protected $ru;
    protected $rd;

    public $tries = 3;
    public $timeout = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($u, $d, $ru, $rd, $userId, $serverId, $serverType)
    {
        $this->onQueue('traffic_log');
        $this->u = $u;
        $this->d = $d;
        $this->ru = $ru;
        $this->rd = $rd;
        $this->userId = $userId;
        $this->serverId = $serverId;
        $this->serverType = $serverType;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Throwable
     */
    public function handle()
    {
        $date = date('Y-m-d');
        $timestamp = strtotime($date);

        DB::beginTransaction();

        /**
         * @var TrafficServerLog $serverLog
         */
        $trafficServerLog = TrafficServerLog::where(TrafficServerLog::FIELD_LOG_AT, '=', $timestamp)
            ->where(TrafficServerLog::FIELD_SERVER_ID, $this->serverId)
            ->first();

        if ($trafficServerLog !== null) {
            $trafficServerLog->addTraffic($this->ru , $this->rd);
        } else {
            $trafficServerLog = new TrafficServerLog();
            $trafficServerLog->addTraffic($this->ru, $this->rd);
            $trafficServerLog->setAttribute(TrafficServerLog::FIELD_SERVER_TYPE, $this->serverType);
            $trafficServerLog->setAttribute(TrafficServerLog::FIELD_SERVER_ID, $this->serverId);
            $trafficServerLog->setAttribute(TrafficServerLog::FIELD_LOG_AT, $timestamp);
            $trafficServerLog->setAttribute(TrafficServerLog::FIELD_LOG_DATE, $date);
        }

        if (!$trafficServerLog->save()) {
            DB::rollBack();
            throw new Exception("server save failed");
        }


        /**
         * @var TrafficUserLog
         */
        $trafficUserLog = TrafficUserLog::where(TrafficUserLog::FIELD_LOG_AT, '=', $timestamp)
            ->where(TrafficUserLog::FIELD_USER_ID, $this->userId)
            ->first();

        if ($trafficUserLog !== null) {
            $trafficUserLog->addTraffic($this->u, $this->d);
        } else {
            $trafficUserLog = new TrafficUserLog();
            $trafficUserLog->addTraffic($this->u, $this->d);
            $trafficUserLog->setAttribute(TrafficUserLog::FIELD_USER_ID, $this->userId);
            $trafficUserLog->setAttribute(TrafficUserLog::FIELD_LOG_AT, $timestamp);
            $trafficUserLog->setAttribute(TrafficUserLog::FIELD_LOG_DATE, $date);
        }

        if (!$trafficUserLog->save()) {
            DB::rollBack();
            throw new Exception("server save failed");
        }

        DB::commit();
    }

}
