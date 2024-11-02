<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UsersImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:users-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from /storage/app/private/users.xlsx to users database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Excel::import(new UsersImport, Storage::path('users.xlsx'));
    }
}
