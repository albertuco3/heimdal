<?php

namespace App\Command;

use App\Entity\StaffUser;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ChangePasswordCommand extends Command
{

    protected static $defaultName = 'app:change-password';

    private UserPasswordEncoderInterface $encoder;

    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->encoder = $encoder;

        $this->entityManager = $entityManager;

        parent::__construct();

        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this->setDescription('Cambia la contraseña de un usuario')
             ->setHelp('Cambia la contraseña de un usuario')
             ->addArgument('username', InputArgument::OPTIONAL, 'Nombre de usuario.')
             ->addArgument('password', InputArgument::OPTIONAL, 'Contraseña.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $helper = $this->getHelper('question');


        $username = $input->getArgument('username');
        if ($username == null) {
            $question = new Question('');
            $output->writeln('Indica el nombre de usuario:');
            $username = $helper->ask($input, $output, $question);
        }

        $pass = $input->getArgument('password');
        if ($pass == null) {
            $output->writeln('Indica la nueva contraseña:');
            $question = new Question('');
            $question->setHidden(true);
            $pass = $helper->ask($input, $output, $question);
        }

        $user = $this->userRepository->findOneBy(['username' => $username]);
        $user->setPassword($this->encoder->encodePassword($user, $pass));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Contraseña cambiada correctamente.');

        return 0;
    }

}
