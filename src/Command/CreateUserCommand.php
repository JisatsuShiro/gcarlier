<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un nouvel utilisateur',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('Création d\'un nouvel utilisateur');

        // Nom
        $nameQuestion = new Question('Nom complet: ');
        $name = $helper->ask($input, $output, $nameQuestion);

        // Email
        $emailQuestion = new Question('Email: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        // Vérifier si l'email existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà!');
            return Command::FAILURE;
        }

        // Mot de passe
        $passwordQuestion = new Question('Mot de passe: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Créer l'utilisateur
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Utilisateur créé avec succès!');
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['Nom', $user->getName()],
                ['Email', $user->getEmail()],
                ['ID', $user->getId()],
            ]
        );

        return Command::SUCCESS;
    }
}
