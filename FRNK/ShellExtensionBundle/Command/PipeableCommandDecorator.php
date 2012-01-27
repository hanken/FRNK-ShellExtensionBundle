<?php

namespace FRNK\ShellExtensionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class PipeableCommandDecorator extends Command{
    protected $command;

    
    protected function configure(){
    }
    
    function __construct(Command $command=null) {
        if (!$command){
            
            return;
        }
                $this->command = $command;
                $this->setName($command->getName());
    }

   
    
     public function setCommand(Command $command)
    {
        $this->command->setCommand($command);
    }
    
    /**
     * Returns a text representation of the command.
     *
     * @return string A string representing the command
     */
    public function asText()
    {
        $messages = array();
            
        foreach (explode("\n", $this->command->asText()) as $message) {
            if (!(strstr($message, "The piped Data"))){
                 $messages[] = $message;
            }
        }
        return implode("\n", $messages);
    }
    
    
    public function addArgument($name, $mode = null, $description = '', $default = null) {
        return $this->command->addArgument($name, $mode, $description, $default);
    }

    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null) {
        return $this->command->addOption($name, $shortcut, $mode, $description, $default);
    }

    public function asXml($asDom = false) {
        return $this->command->asXml($asDom);
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        return $this->command->execute( $input,  $output);
    }

    public function getAliases() {
        return $this->command->getAliases();
    }

    public function getApplication() {
        return $this->command->getApplication();
    }

    public function getDefinition() {
        return $this->command->getDefinition();
    }

    public function getDescription() {
        return $this->command->getDescription();
    }

    public function getHelp() {
        return $this->command->getHelp();
    }

    public function getHelper($name) {
        return $this->command->getHelper($name);
    }

    public function getHelperSet() {
        return $this->command->getHelperSet();
    }

    public function getName() {
        return $this->command->getName();
    }

    public function getProcessedHelp() {
        return $this->command->getProcessedHelp();
    }

    public function getSynopsis() {
        return $this->command->getSynopsis();
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->command->initialize( $input,  $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output) {
        $this->command->interact( $input,  $output);
    }

    public function isEnabled() {
        return $this->command->isEnabled();
    }

    public function run(InputInterface $input, OutputInterface $output) {
        return $this->command->run( $input,  $output);
    }

    public function setAliases($aliases) {
        return $this->command->setAliases($aliases);
    }

    public function setApplication(Application $application = null) {
        $this->command->setApplication( $application);
    }

    public function setCode(\Closure $code) {
        return $this->command->setCode( $code);
    }

    public function setDefinition($definition) {
        return $this->command->setDefinition($definition);
    }

    public function setDescription($description) {
        return $this->command->setDescription($description);
    }

    public function setHelp($help) {
        return $this->command->setHelp($help);
    }

    public function setHelperSet(HelperSet $helperSet) {
        $this->command->setHelperSet( $helperSet);
    }

  

    
}

?>
