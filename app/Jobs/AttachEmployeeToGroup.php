<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Company\Group\AddEmployeeToGroup;

class AttachEmployeeToGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new AddEmployeeToGroup)->execute([
            'company_id' => $this->data['company_id'],
            'author_id' => $this->data['author_id'],
            'employee_id' => $this->data['employee_id'],
            'group_id' => $this->data['group_id'],
        ]);
    }
}
