<?php

namespace App\Command;

use App\Entity\StaffUser;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserCommand extends Command
{

    protected static $defaultName = 'app:create-user';

    private UserPasswordEncoderInterface $encoder;

    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager)
    {
        $this->encoder = $encoder;

        $this->entityManager = $entityManager;

        parent::__construct();

    }

    protected function configure()
    {
        $this->setDescription('Crea un nuevo usuario')
             ->setHelp('Crea un nuevo usuario')
             ->addArgument('username', InputArgument::OPTIONAL, 'Nombre de usuario.')
             ->addArgument('role', InputArgument::OPTIONAL, 'Rol del usuario.')
             ->addArgument('firstName', InputArgument::OPTIONAL, 'Nombre.')
             ->addArgument('lastName', InputArgument::OPTIONAL, 'Apellidos.')
             ->addArgument('password', InputArgument::OPTIONAL, 'Contraseña.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Vamos a crear un nuevo usuario:');
        $helper = $this->getHelper('question');
        $first_name = $input->getArgument('firstName');
        $last_name = $input->getArgument('lastName');

        if ($first_name == null) {
            $output->writeln('Indica el nombre:');
            $question = new Question('');
            $first_name = $helper->ask($input, $output, $question);
        }
        if ($last_name == null) {
            $output->writeln('Indica los apellidos:');
            $question = new Question('');
            $last_name = $helper->ask($input, $output, $question);
        }
        $username = $input->getArgument('username');
        if ($username == null) {
            $question = new Question('');
            $output->writeln('Indica el nombre de usuario:');
            $username = $helper->ask($input, $output, $question);
        }
        $rol = $input->getArgument('role');
        if ($rol == null) {
            $output->writeln('Indica el rol que tendrá el usuario:');
            $question = new Question('');
            $rol = $helper->ask($input, $output, $question);
        }
        $pass = $input->getArgument('password');
        if ($pass == null) {
            $output->writeln('Indica la contraseña del usuario:');
            $question = new Question('');
            $question->setHidden(true);
            $pass = $helper->ask($input, $output, $question);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles([$rol]);
        $user->setFirstName($first_name);
        $user->setLastName($last_name);
        $user->setPassword($this->encoder->encodePassword($user, $pass));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Usuario creado correctamente.');

        return 0;
    }

}
