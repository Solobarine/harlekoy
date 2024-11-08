<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Faker\Factory as Faker;

class RandomlyUpdateUserDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:randomly-update-user-details {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Randomly updates User details like firstName, lastName and timezone';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create();

        $user_id = $this->argument('user_id');

        $user = User::find($user_id);

        $timezones = ['CET', 'CST', 'GMT+1'];

        if (!$user) {
            $this->error('User not Found');
            return 1;
        }

        $user->firstName = $faker->firstName();
        $user->lastName = $faker->lastName();
        $user->timezone = $timezones[rand(0, count($timezones) - 1)];
        $user->save();

        $this->info("User's details updated successfully: ");
        $this->info('---------------------------------');
        $this->info("NEW DETAILS FOR USER WITH ID #{$user_id}");
        $this->info('---------------------------------');
        $this->newLine();
        $this->table(
            ['Fields', 'Value'],
            [
                ["Firstname", $user->firstName],
                ["Lastname", $user->lastName],
                ["Timezone", $user->timezone]
            ]
        );
        $this->newLine();
        $this->info('.................................');

        return 0;
    }
}
