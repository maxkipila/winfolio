<?php

namespace App\Console\Commands;

use App\Jobs\MoveMediaToDiskJob;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TransferFilesToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'move-media-to-disk {fromDisk} {toDisk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $diskNameFrom = $this->argument('fromDisk');
        $diskNameTo = $this->argument('toDisk');

        $this->checkIfDiskExists($diskNameFrom);
        $this->checkIfDiskExists($diskNameTo);

        // $images = Media::where('disk', $diskNameFrom)
        //     ->where('model_type', 'App\Models\Image')
        //     ->count();

        // dd($images);

        Media::where('disk', $diskNameFrom)
            // ->take(1)
            // ->where('model_type', 'App\Models\Image')
            ->chunk(1000, function ($medias) use ($diskNameFrom, $diskNameTo) {
                /** @var Media $media */
                foreach ($medias as $media) {
                    dispatch(new MoveMediaToDiskJob($media, $diskNameFrom, $diskNameTo));
                }
            });
    }

    /**
     * Check if disks are set in the config/filesystem.
     *
     * @param $diskName
     * @throws \Exception
     */
    private function checkIfDiskExists($diskName)
    {
        if (!config("filesystems.disks.{$diskName}.driver")) {
            throw new \Exception("Disk driver for disk `{$diskName}` not set.");
        }
    }
}
