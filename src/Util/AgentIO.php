<?php

namespace ArnaudDelgerie\AiToolAgent\Util;

use Symfony\Component\Console\Style\SymfonyStyle;

class AgentIO
{
    public function __construct(
        public SymfonyStyle $io,
        public string       $agentLabel = 'ToolAgent',
        public string       $userLabel = 'You',
        public string       $agentColor = 'blue'
    ) {}

    public function ask(string $question, ?string $default = null, ?callable $validator = null): ?string
    {
        $this->io->writeln('<fg='.$this->agentColor.'>'.$this->agentLabel.':</> '.$question);
        return $this->io->ask($this->userLabel, $default, $validator);
    }

    public function confirm(string $question, bool $default = true): bool
    {
        $this->io->writeln('<fg='.$this->agentColor.'>'.$this->agentLabel.':</> '.$question);
        return $this->io->confirm($this->userLabel, $default);
    }

    public function text(string $content): void
    {
        $this->io->writeln('<fg='.$this->agentColor.'>'.$this->agentLabel.':</> '.$content.PHP_EOL);
    }
    
    public function alert(string $content): void
    {
        $this->io->writeln('<fg='.$this->agentColor.'>'.$this->agentLabel.':</> <fg=red>'.$content.'</>'.PHP_EOL);
    }

    public function logUsage(AgentUsageReport $usageReport): void
    {
        $this->text('Usage report');
        $table = $this->io->createTable();
        $table->setStyle('box');
        $table->setColumnWidth(0, 20);
        $table->setColumnWidth(1, 20);
        $table->setColumnWidth(2, 20);
        $table->setHeaders(['Nb request', 'Total prompt tokens', 'Total completion tokens']);
        $table->setRows([[$usageReport->getNbRequest(), $usageReport->getPromptTokens(), $usageReport->getCompletionTokens()]]);
        $table->render();
        $this->io->text('');
    }
}