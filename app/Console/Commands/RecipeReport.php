<?php

namespace App\Console\Commands;

use App\Mail\RecipeListMail;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RecipeReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:recipe-report {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A report of all recipes';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $sendToEmail = $this->option('email');

        $recipes = Recipe::all();


        Mail::to($sendToEmail)->send(new RecipeListMail($recipes));

        return Command::SUCCESS;
    }
}