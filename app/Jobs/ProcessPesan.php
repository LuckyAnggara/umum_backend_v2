<?php

namespace App\Jobs;

use App\Http\Controllers\PesanController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessPesan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $wa, $pesan;

    /**
     * Create a new job instance.
     */
    public function __construct($wa, $pesan)
    {
        $this->wa = $wa;
        $this->pesan = $pesan;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $dataSending = array();
            $dataSending["api_key"] = 'PZRWB4JG5LTLT2ZV';
            $dataSending["number_key"] = 'n74BlBzROOvfHNwk';
            $dataSending["phone_no"] = PesanController::formatWa($this->wa);
            $dataSending["message"] = $this->pesan;
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 0,
            ])->withHeaders([
                'Content-Type: application/json'
            ])
                ->send('POST', 'https://api.watzap.id/v1/send_message', [
                    'body' => json_encode($dataSending)
                ])->json();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
