<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user in the database',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'User email address')
            ->addArgument('firstname', InputArgument::OPTIONAL, 'User first name')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'User last name')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Set user as admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // Get email
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Please enter the user email: ');
            $question->setValidator(function ($value) {
                if (empty($value)) {
                    throw new \Exception('Email cannot be empty');
                }
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('Invalid email format');
                }
                return $value;
            });
            $email = $helper->ask($input, $output, $question);
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln('<error>A user with this email already exists.</error>');
            return Command::FAILURE;
        }

        // Get first name
        $firstname = $input->getArgument('firstname');
        if (!$firstname) {
            $question = new Question('Please enter the user first name: ');
            $firstname = $helper->ask($input, $output, $question);
        }

        // Get last name
        $lastname = $input->getArgument('lastname');
        if (!$lastname) {
            $question = new Question('Please enter the user last name: ');
            $lastname = $helper->ask($input, $output, $question);
        }

        // Get password
        $password = $input->getArgument('password');
        if (!$password) {
            $question = new Question('Please enter the user password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }

        // Create user
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Set roles
        if ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
            $output->writeln('<info>User role set to ADMIN</info>');
        }

        // Save to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>User created successfully!</info>');
        $output->writeln('');
        $output->writeln('<comment>User Details:</comment>');
        $output->writeln('  Email: ' . $user->getEmail());
        $output->writeln('  First Name: ' . $user->getFirstname());
        $output->writeln('  Last Name: ' . $user->getLastname());
        $output->writeln('  Roles: ' . implode(', ', $user->getRoles()));

        return Command::SUCCESS;
    }
}
