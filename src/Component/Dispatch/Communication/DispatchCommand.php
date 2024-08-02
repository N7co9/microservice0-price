<?php
declare(strict_types=1);

namespace App\Component\Dispatch\Communication;

use App\Component\Dispatch\Business\Model\Dispatch;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:dispatch',
    description: 'Sends Stock Update Messages as ProductDTOs.',
    aliases: ['app:send'],
    hidden: false
)]
class DispatchCommand extends Command
{
    private Dispatch $dispatcher;

    public function __construct(Dispatch $dispatcher)
    {
        parent::__construct();
        $this->dispatcher = $dispatcher;
    }

    protected function configure(): void
    {
        $this->addArgument('fileLocations', InputArgument::IS_ARRAY, 'The locations of XML files to process (separate multiple locations with a space)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileLocations = $input->getArgument('fileLocations');

        if (empty($fileLocations)) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new Question('Please provide the locations of XML files to process (separate multiple locations with a space): ');
            $fileLocations = explode(' ', $helper->ask($input, $output, $question));
        }

        $output->writeln([
            'Dispatching Upstream Messages',
            '============================',
            '',
        ]);

        try {
            $this->dispatcher->dispatch($fileLocations);
            $output->writeln('Files processed and archived successfully.');
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}