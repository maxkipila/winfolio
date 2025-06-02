<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class MoveMediaToDiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $diskNameFrom;
    public $diskNameTo;
    public $media;

    /**
     * Create a new job instance.
     *
     * @param  Media  $media
     * @param $diskNameFrom
     * @param $diskNameTo
     */
    public function __construct(Media $media, $diskNameFrom, $diskNameTo)
    {
        $this->media = $media;
        $this->diskNameFrom = $diskNameFrom;
        $this->diskNameTo = $diskNameTo;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        // check if media still on same disk
        if ($this->media->disk != $this->diskNameFrom) {
            throw new \Exception("Current media disk `{$this->media->disk}` is not the expected `{$this->diskNameFrom}` disk.");
        }

        // generate the path to the media
        $mediaPath = PathGeneratorFactory::create($this->media)->getPath($this->media);

        $diskFrom = Storage::disk($this->diskNameFrom);
        $filesInDirectory = $diskFrom->allFiles($mediaPath);

        $jobs = collect();

        // dispatch jobs foreach file (recursive) in the storage map for the media item
        foreach ($filesInDirectory as $fileInDirectory) {
            $jobs->push(new MoveMediaFileToDiskJob($this->diskNameFrom, $this->diskNameTo, $fileInDirectory));
        }

        if ($jobs->count() == 0) {
            // throw new \Exception('No jobs found to dispatch.');
            return;
        }

        // dispatch job to update the media disk in the database so it will load from the new storage disk
        $jobs->push(new MoveMediaUpdateDiskJob($this->media, $this->diskNameTo));

        Bus::chain($jobs)->dispatch();
    }
}
