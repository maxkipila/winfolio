<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CollectMediaToMoveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $diskNameFrom;
    public $diskNameTo;
    public $offset;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($diskNameFrom, $diskNameTo, $offset = null)
    {
        $this->diskNameFrom = $diskNameFrom;
        $this->diskNameTo = $diskNameTo;
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recordsToMove = Media::where('disk', $this->diskNameFrom);

        if ($this->offset) {
            $recordsToMove->where('id', '>', $this->offset);
        }

        $recordsToMove = $recordsToMove->limit($limit = 1000)
            ->get();

        if ($recordsToMove->count() == $limit) {
            dispatch(new CollectMediaToMoveJob($this->diskNameFrom, $this->diskNameTo, $recordsToMove->last()->id));
        }
    }
}
