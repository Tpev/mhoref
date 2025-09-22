<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates/updates users with the same groups, each using
     * firstname.lastname.demo! style passwords (lowercase).
     *
     * Also creates a personal team for each user if not already present.
     *
     * @return void
     */
    public function run()
    {
        // All users share the same groups
        $commonGroups = ["nurse", "provider", "admin", "physician", "social_worker", "ma", "pt_ot"];

        // Manually assign passwords in the desired "firstname.lastname.demo!" format (all lowercase).
        $usersData = [
            // 1) Peverelli T.
            [
                'email'    => 'peverelli.t@gmail.com',
                'name'     => 'Peverelli T.',
                'password' => Hash::make('testtest'),
                'group'    => $commonGroups,
            ],
            // 2) Charles Petrini Poli
            [
                'email'    => 'charlespp42@gmail.com',
                'name'     => 'Charles Petrini Poli',
                // folded the middle name into the last name portion
                'password' => Hash::make('charles.petrini.poli.demo!'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'Julie.Evans@mainehealth.org',
                'name'     => 'Julie Evans',
                'password' => Hash::make('Julie.Evans.demo!'),
                'group'    => $commonGroups,
            ],
    
            [
                'email'    => 'Sophia.Bazzini@mainehealth.org',
                'name'     => 'Sophia Bazzini',
                'password' => Hash::make('Sophia.Bazzini.demo!'),
                'group'    => $commonGroups,
            ],
    
            [
                'email'    => 'Christopher.sawyer@mainehealth.org',
                'name'     => 'Christopher sawyer',
                'password' => Hash::make('Christopher.Sawyer.demo!'),
                'group'    => $commonGroups,
            ],
            // 6) T Peverelli
            [
                'email'    => 'tpeverelli@hub.healthcare',
                'name'     => 'T Peverelli',
                'password' => Hash::make('t.peverelli.demo!'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'Brian.Roux@mainehealth.org',
                'name'     => 'Brian Roux',
                'password' => Hash::make('Brian.Roux.demo!'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'Bethany.fulton@mainehealth.org',
                'name'     => 'Amanda Roberts',
                'password' => Hash::make('Bethany.fulton.demo!'),
                'group'    => $commonGroups,
            ],

            [
                'email'    => 'Ashley.Anderson@mainehealth.org',
                'name'     => 'Ashley Anderson',
                'password' => Hash::make('ashley.anderson.demo!'),
                'group'    => $commonGroups,
            ],
            // 10) Molly Dow
            [
                'email'    => 'Molly.Dow@mainehealth.org',
                'name'     => 'Molly Dow',
                'password' => Hash::make('molly.dow.demo!'),
                'group'    => $commonGroups,
            ],
            // 11) Brandon McCrossin
            [
                'email'    => 'Brandon.McCrossin@mainehealth.org',
                'name'     => 'Brandon McCrossin',
                'password' => Hash::make('brandon.mccrossin.demo!'),
                'group'    => $commonGroups,
            ],
            // 12) Heidi Wierman
            [
                'email'    => 'Heidi.Wierman@mainehealth.org',
                'name'     => 'Heidi Wierman',
                'password' => Hash::make('heidi.wierman.demo!'),
                'group'    => $commonGroups,
            ],
            // 13) Richard Marino
            [
                'email'    => 'Richard.Marino@mainehealth.org',
                'name'     => 'Richard Marino',
                'password' => Hash::make('richard.marino.demo!'),
                'group'    => $commonGroups,
            ],
        ];

        foreach ($usersData as $userData) {
            // Create or update each user by email
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => $userData['password'],
                    'group'    => $userData['group'],
                ]
            );

            $this->command->info('UserSeeder: User with email ' . $userData['email'] . ' created/updated.');

            // Create personal team if it doesn't exist
            if (!$user->ownedTeams()->where('personal_team', true)->exists()) {
                $firstName = explode(' ', $user->name, 2)[0];
                $team = $user->ownedTeams()->create([
                    'name'          => "{$firstName}'s Team",
                    'personal_team' => true,
                ]);

                $this->command->info("UserSeeder: Personal team '{$team->name}' created for '{$user->email}'.");
            } else {
                $this->command->info("UserSeeder: Personal team already exists for '{$user->email}'.");
            }
        }
    }
}
