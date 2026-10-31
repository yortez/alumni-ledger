<?php

namespace Database\Seeders;

use App\Models\Employment;
use App\Models\Graduate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoAlumniSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $passwordHash = Hash::make('password');
        $programCodes = ['CS', 'IT', 'ENVS', 'BA', 'EDU'];
        $employmentStatuses = ['employed_local', 'employed_abroad', 'self_employed'];
        $industries = ['Information technology', 'Education', 'Healthcare', 'Finance', 'Public administration'];

        DB::transaction(function () use ($passwordHash, $programCodes, $employmentStatuses, $industries): void {
            foreach (range(1, 100) as $index) {
                $faker = fake();
                $studentNumber = sprintf('DEMO-ALUMNI-%04d', $index);
                $graduationYear = 2015 + (($index - 1) % 10);
                $firstName = $faker->firstName();
                $middleName = $faker->firstName();
                $lastName = $faker->lastName();
                $fullName = implode(' ', [$firstName, $middleName, $lastName]);
                $programCode = $programCodes[($index - 1) % count($programCodes)];

                Graduate::query()->updateOrCreate(
                    ['student_number' => $studentNumber],
                    [
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'program_code' => $programCode,
                        'batch_year' => $graduationYear,
                        'latin_honor' => Graduate::LATIN_HONORS[$index % count(Graduate::LATIN_HONORS)],
                    ],
                );

                $user = User::query()->updateOrCreate(
                    ['student_number' => $studentNumber],
                    [
                        'name' => $fullName,
                        'username' => sprintf('demoalumni%04d', $index),
                        'email' => sprintf('alumni%04d@example.test', $index),
                        'password' => $passwordHash,
                    ],
                );
                $user->forceFill([
                    'email_verified_at' => now(),
                    'is_admin' => false,
                    'role_id' => null,
                ])->save();

                $employmentStatus = $employmentStatuses[($index - 1) % count($employmentStatuses)];
                $city = $faker->city();
                $country = $employmentStatus === 'employed_abroad'
                    ? $faker->randomElement(['Australia', 'Japan', 'Singapore', 'United States'])
                    : 'Philippines';
                $jobTitle = $faker->jobTitle();
                $employer = $faker->company();
                $industry = $industries[($index - 1) % count($industries)];

                $profile = $user->profile()->updateOrCreate([], [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'birthdate' => $faker->dateTimeBetween(
                        ($graduationYear - 27).'-01-01',
                        ($graduationYear - 19).'-12-31',
                    )->format('Y-m-d'),
                    'sex' => $faker->randomElement(['female', 'male', 'other', 'prefer_not_to_say']),
                    'civil_status' => $faker->randomElement(['single', 'married', 'widowed', 'divorced', 'separated']),
                    'phone' => sprintf('+63 917 %03d %04d', $index, 1000 + $index),
                    'current_address' => $faker->address(),
                    'permanent_address' => $faker->address(),
                    'job_title' => $jobTitle,
                    'employer' => $employer,
                    'industry' => $industry,
                    'employment_status' => $employmentStatus,
                    'city' => $city,
                    'country' => 'Philippines',
                    'linkedin_url' => sprintf('https://www.linkedin.com/in/demoalumni%04d', $index),
                    'bio' => $faker->paragraph(),
                    'completed_at' => now(),
                ]);

                $employmentDetails = [
                    'job_title' => $jobTitle,
                    'employer' => $employer,
                    'location' => $city,
                    'start_date' => $faker->dateTimeBetween(
                        $graduationYear.'-06-01',
                        now()->subMonth()->toDateString(),
                    )->format('Y-m-d'),
                    'is_current' => true,
                    'description' => $faker->sentence(),
                    'employment_status' => $employmentStatus,
                    'sector' => $employmentStatus === 'self_employed'
                        ? 'self_employed'
                        : $faker->randomElement(['private', 'public', 'non_profit']),
                    'industry' => $industry,
                    'job_level' => $employmentStatus === 'self_employed'
                        ? 'self_employed'
                        : $faker->randomElement(['rank_file', 'supervisory', 'managerial', 'executive']),
                    'monthly_income' => $faker->randomElement(array_keys(Employment::MONTHLY_INCOME_RANGES)),
                    'country' => $country,
                    'months_to_employment' => $faker->numberBetween(0, 24),
                    'course_relevance' => $faker->randomElement(array_keys(Employment::COURSE_RELEVANCE_OPTIONS)),
                ];

                $profile->employments()->updateOrCreate(['is_current' => true], $employmentDetails);
            }
        });
    }
}
