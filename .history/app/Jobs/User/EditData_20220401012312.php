<?php

namespace App\Jobs\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EditData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $rules = [
        'name' => 'required',
        'email' => 'required',
        'password' => 'required',
        'level' => 'required',
    ];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        Validator::make($data, $this->rules);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
