<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MoveMediaUpdateDiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $media;
    public $diskNameTo;

    /**
     * Create a new job instance.
     *
     * @param  Media  $media
     * @param $diskNameTo
     */
    public function __construct(Media $media, $diskNameTo)
    {
        $this->media = $media;
        $this->diskNameTo = $diskNameTo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->media->disk = $this->diskNameTo;
        $this->media->conversions_disk = $this->diskNameTo;

        $this->media->save();
    }
}
