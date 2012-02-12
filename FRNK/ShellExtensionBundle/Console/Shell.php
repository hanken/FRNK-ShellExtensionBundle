<?php

namespace FRNK\ShellExtensionBundle\Console;

use Symfony\Bundle\FrameworkBundle\Console\Shell as BaseShell;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use FRNK\ShellExtensionBundle\Output\BufferedOutput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputOption;

class Shell extends BaseShell {

    private $application;
    private $history;
    private $output;
    private $hasReadline;
    private $prompt;

    /**
     * Constructor.
     *
     * If there is no readline support for the current PHP executable
     * a \RuntimeException exception is thrown.
     *
     * @param Application $application An application instance
     *
     * @throws \RuntimeException When Readline extension is not enabled
     */
    public function __construct(Application $application) {
        $this->hasReadline = function_exists(/'readline');
        $this->application = $application;
        $this->history = getenv('HOME') . '/.history_' . $application->getName();
        $this->output = new ConsoleOutput();
        $this->prompt = '<info>' . $application->getName() . '#</info> ';
     //   parent::__construct($application);
    }

    public function get($id) {
        return $this->application->getKernel()->getContainer()->get($id);
    }

    /**
     * Runs the shell.
     */
    public function run() {
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(true);

        if ($this->hasReadline) {
            readline_read_history($this->history);
            readline_completion_function(array($this, 'autocompleter'));
        }

        $this->output->writeln($this->getHeader());
        do {
            $exit = false;
            $command = $this->readline();

            
            if ($this->isExitCommand($command, $this->output)) {
                $exit = true;
                
            } else if (!empty($command)) {
                $run = false;
                $pipedCommands = explode("|", $command);
                $pipesToGo = count($pipedCommands) - 1;
                foreach ($pipedCommands as $subCommand) {
                    $command = trim($subCommand);
                    $pipeContent = "";
                    $piped = $run;
                    $run = false;
                    
                    if ($piped) {
                        $pipeContent = $this->handlePipe($bufferedOutput);
                        $pipesToGo--;
                    }
                    $bufferedOutput = new BufferedOutput($this->output, ($pipesToGo > 0));

                   
                
                    if ($this->isShellCommand($command)) {
                        $this->runShellCommand($command, $bufferedOutput);
                        $run = true;
                    }

                    if ((!$run) && $this->isExtensionCommand($command)) {
                        if ($piped) {
                            $command = $command . " --pipe '" . $pipeContent . "'";
                        }
                        
                        $this->runExtensionCommand($command, $bufferedOutput);
                        $run = true;
                    }

                
                    if ($piped) {
                        
                        $command = '$pipe = $pipeContent;$lines=explode(\'\n\', $pipe);' . $command;
                    }
                    if ((!$run) && $this->validatePhpCode($command)) {
                        try {
                            ob_start();
                            $eval = eval($command);
                            $result = ob_get_contents();
                            $bufferedOutput->writeln($result);
                            ob_clean();
                        } catch (Exception $ex) {
                            $this->writeException($ex, $bufferedOutput);
                        }
                        $run = true;
                    } 
                    
                    
                    if (!$run) {
                       $this->output->writeln("<error>Computer says: No!</error>\n<comment>Command \"" .trim($subCommand)."\" is no valid command nor does it apear to be valid php code</comment>");
                    }
                }

                if ($this->hasReadline) {
                    readline_add_history($command);
                    readline_write_history($this->history);
                }
            }
        } while (!$exit);
        $this->output->writeln("See you again");
    }
 
    
    protected function validatePhpCode($code) {
        return @eval('return true;' . $code);
    }

    protected function writeException($ex, &$output) {
        $output->writeln("<error>" . $ex->getMessage() . "</error>");
    }

    protected function isExtensionCommand($command){
        return $this->isShellCommand("extension:".$command);
    }
    
    protected function runExtensionCommand($command, &$output){
        return $this->runShellCommand("extension:".$command, $output);
    }
    
    protected function isShellCommand($command) {
        $userCommandParts = explode(' ', $command);
        foreach ($this->application->all() as $name => $shellCommand) {
            if ($name == $userCommandParts[0]) {
                return true;
            }
        }
        return false;
    }

    /**
     * runs a normal shell command. 
     * @param string $command
     * @param OutputInterface $output
     * @return boolean 
     */
    protected function runShellCommand($command, &$output) {
        try {
            if (0 == $this->application->run(new StringInput($command), $output)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {

            $this->writeException($ex, $output);
            return false;
        }
    }

    /**
     * implodes the piped content; 
     * @param BufferedOutput $bufferedOutput
     * @return string 
     */
    protected function handlePipe(BufferedOutput $bufferedOutput) {
        return implode('\n', $bufferedOutput->getMessages());
    }

    /**
     * Checks if the entered command is an exit command or ^D 
     * @param string $command
     * @param OutputInterface $output
     * @return boolean 
     */
    protected function isExitCommand($command, OutputInterface $output) {

        if (false === $command) {
            $output->writeln("");
            return true;
        } else if ($command == "q" || $command == "quit" || $command == "exit") {
            return true;
        }
        return false;
    }

    
    
    /**
     * Tries to return autocompletion for the current entered text.
     *
     * @param string $text The last segment of the entered text
     * @return Boolean|array A list of guessed strings or true
     */
    private function autocompleter($text)
    {  
        $info = readline_info();
        $text = substr($info['line_buffer'], 0, $info['end']);

        if ($info['point'] !== $info['end']) {
            return true;
        }

        // task name?
        if (false === strpos($text, ' ') || !$text) {
            return array_keys($this->application->all());
        }

        // options and arguments?
        try {
            $command = $this->application->find(substr($text, 0, strpos($text, ' ')));
        } catch (\Exception $e) {
            return true;
        }

        $list = array('--help');
        foreach ($command->getDefinition()->getOptions() as $option) {
            $list[] = '--'.$option->getName();
        }

        return $list;
    }


    /**
     * Reads a single line from standard input.
     *
     * @return string The single line from standard input
     */
    private function readline() {
        if ($this->hasReadline) {
            $line = readline($this->prompt);
        } else {
            $this->output->write($this->prompt);
            $line = fgets(STDIN, 1024);
            $line = (!$line && strlen($line) == 0) ? false : rtrim($line);
        }

        return $line;
    }

    
    /**
     * Returns the shell header.
     *
     * @return string The header string
     */
    protected function getHeader() {

        return <<<EOF
<info>
      _____                  __                  ___
     / ____|                / _|                |__ \
    | (___  _   _ _ __ ___ | |_ ___  _ __  _   _   ) |
     \___ \| | | | '_ ` _ \|  _/ _ \| '_ \| | | | / /
     ____) | |_| | | | | | | || (_) | | | | |_| |/ /_
    |_____/ \__, |_| |_| |_|_| \___/|_| |_|\__, |____|
             __/ |                          __/ |
            |___/                          |___/

</info>
Welcome to the <info>{$this->application->getName()}</info> shell (<comment>{$this->application->getVersion()}</comment>).

At the prompt, type <comment>help</comment> for some help,
or <comment>list</comment> to get a list available commands.

To exit the shell, type <comment>quit</comment>.

EOF;

    }

}

?>
