<?php

namespace App\Console\Commands;

use App\Models\Scripts\Sysupdate;
use Illuminate\Console\Command;

class RunScripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmd:runscripts {--v=}';
    //protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $version = $this->option('v');
        $version = 'v'.md5($version);
        $service = Sysupdate::factory($version);
        $res = $service->upgrade();
        echo json_encode($res);
//        $className = 'v'.md5($version);
//        $modelPath = '\App\Models\Scripts\\'.$className;
        //echo $modelPath;exit();
    }
}
