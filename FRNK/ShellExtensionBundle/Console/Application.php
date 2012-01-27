<?php

namespace FRNK\ShellExtensionBundle\Console;
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;
use FRNK\ShellExtensionBundle\Console\Shell as ExtendedShell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Console\Command\Command;
use FRNK\ShellExtensionBundle\Command\PipeableCommandDecorator;

class Application extends BaseApplication {
    
    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);
    
    }

    
    
    public function add(Command $command){
//        $input = new StringInput("extension:".$command);
//        
//         $name = $input->getFirstArgument('command');
//                $this->application->find($name)->addOption('pipe', null, InputOption::VALUE_REQUIRED, "The piped Data", '');
//
        if ($command && $command->getName()){
            $command = new PipeableCommandDecorator($command);
            $command->addOption('pipe', null, InputOption::VALUE_REQUIRED, "The piped Data", '');
////    
//        
        return parent::add($command);
        }
    }
    
//     public function run(InputInterface $input = null, OutputInterface $output = null)
//    {
//         $name = $input->getFirstArgument('command');
//         $command = $this->find($name);
//         $command = 
//         
//     }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();

        if (true === $input->hasParameterOption(array('--shell', '-s'))) {
            $shell = new ExtendedShell($this);
            $shell->run();

            return 0;
        }

        return parent::doRun($input, $output);
    }

}

?>
