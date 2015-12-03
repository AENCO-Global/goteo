<?php
/*
 * This file is part of the Goteo Package.
 *
 * (c) Platoniq y Fundación Goteo <fundacion@goteo.org>
 *
 * For the full copyright and license information, please view the README.md
 * and LICENSE files that was distributed with this source code.
 */

namespace Goteo\Console\Command;

use Goteo\Console\ConsoleEvents;
use Goteo\Console\Event\FilterProjectEvent;
use Goteo\Library\Currency;
use Goteo\Model\Invest;
use Goteo\Model\Project;

use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RoundCommand extends AbstractCommand {

	protected function configure() {
		$this->setName("endround")
		     ->setDescription("Project status changer for 1st and 2on round")
		     ->setDefinition(array(
				new InputOption('update', 'u', InputOption::VALUE_NONE, 'Actually does the job. If not specified, nothing is done, readonly process.'),
				new InputOption('project', 'p', InputOption::VALUE_OPTIONAL, 'Only processes the specified Project ID'),
				new InputOption('skip-invests', 's', InputOption::VALUE_NONE, 'Do not processes Invests returns'),
			))
		->setHelp(<<<EOT
This script proccesses active projects reaching ending rounds.
A failed project will change his status to failed.
A successful project which reached his first round will start a second round.
A successful project which reached his second round will end his invest life time.

Usage:

Processes pending projects in read-only mode
<info>./console endround</info>

Processes pending projects and write operations to database
<info>./console endround --update</info>

Processes projects demo-project only and write operations to database
<info>./console endround --project demo-project --update</info>

Processes projects demo-project only and write operations to database but
does not execute refunds on the related invests
<info>./console endround --project demo-project --skip-invests --update</info>


EOT
		)
		;

	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$update       = $input->getOption('update');
		$project_id   = $input->getOption('project');
		$skip_invests = $input->getOption('skip-invests');

		if ($project_id) {
			$output->writeln("<comment>Processing Project [$project_id]:</comment>");
			$projects = [Project::get($project_id)];
		} else {
			$output->writeln('<comment>Processing Active Projects:</comment>');

			// Active projects
			$projects = Project::getList(['status' => Project::STATUS_IN_CAMPAIGN], null, 0, 10000);
		}

		if (!$projects) {
			$this->info("No projects found");
			return;
		}

		$symbol    = Currency::getDefault('id');
		$processed = 0;
		foreach ($projects as $project) {
			if ((int) $project->status !== Project::STATUS_IN_CAMPAIGN) {
				$this->debug("Skipping status [{$project->status}] from PROJECT: {$project->id}. Only projects IN CAMPAIGN will be processed");
				continue;
			}

			// Make sure amounts are correct
			$project->amount = Invest::invested($project->id);
			$percent         = $project->getAmountPercent();

			$this->info("Processing project in campaign", [$project, "project_days_active" => $project->days_active, 'project_days_round1'  => $project->days_round1,
                    'project_days_round2'  => $project->days_round2, "percent" => $percent]);

			// a los 5, 3, 2, y 1 dia para finalizar ronda
			if ($project->round > 0 && in_array((int) $project->days, array(5, 3, 2, 1))) {
				$this->notice("Public feed due remaining {$project->days} days to end of round {$project->round}", [$project]);
				if ($update) {
					// dispatch ending event, will generate a feed entry if needed
					$project = $this->dispatch(ConsoleEvents::PROJECT_ENDING, new FilterProjectEvent($project))->getProject();
				}
			}

			// Check project's health
			if ($project->days_active >= $project->days_round1) {
				// si no ha alcanzado el mínimo, pasa a estado caducado
				if ($project->amount < $project->mincost) {
					$this->warning("Archiving project. It has FAILED on achieving the minimum amount", [$project]);
					if ($update) {
						// dispatch ending event, will generate a feed entry if needed
						$project = $this->dispatch(ConsoleEvents::PROJECT_FAILED, new FilterProjectEvent($project))->getProject();
					}

					if ($skip_invests) {
						$this->warning("Skipping Invests refunds as required", [$project]);
					} else {
						// Execute "console refund"  command for returning invests
						// refund Invests for this project
						$command = $this->getApplication()->find('refund');

						// Changes the name of the logger:
						// $command->addLogger(new \Monolog\Logger('refund', $this->getLogger()->getHandlers(), $this->getLogger()->getProcessors()), 'refund');
						// lOG UNDER THE SAME NAME
						$command->addLogger($this->getLogger(), 'refund');

						$arguments = array(
							'command'   => 'refund',
							'--project' => $project->id,
							'--update'  => $update,
						);

						$returnCode = $command->run(new ArrayInput($arguments), $output);
					}
				} else {
					if ($project->one_round) {
                        if(empty($project->success)) {
    						// one round only project
    						$this->notice('Ending round for one-round-only project', [$project, 'project_days_round1' => $project->days_round1]);
    						if ($update) {
    							// dispatch passing event, will generate a feed entry if needed
    							$project = $this->dispatch(ConsoleEvents::PROJECT_ONE_ROUND, new FilterProjectEvent($project))->getProject();
    						}
                        }
					} elseif ($project->days_active >= $project->days_total) {
						// 2 rounds project, end of life
						$this->notice('Ending second round for 2-rounds project', [$project, 'project_days_active' => $project->days_active, 'project_days_total' => $project->days_total]);
						if ($update) {
							// dispatch passing event, will generate a feed entry if needed
							$project = $this->dispatch(ConsoleEvents::PROJECT_ROUND2, new FilterProjectEvent($project))->getProject();
						}

					} elseif (empty($project->passed)) {
						// 2 rounds project, 1srt round passed
						$this->notice('Ending first round for 2-rounds project', [$project, 'project_days_round1' => $project->days_round1, 'project_days_total' => $project->days_total]);
						if ($update) {
							// dispatch passing event, will generate a feed entry if needed
							$project = $this->dispatch(ConsoleEvents::PROJECT_ROUND1, new FilterProjectEvent($project))->getProject();
						}

					} else {
						// este caso es lo normal estando en segunda ronda
						$this->debug('Project in second round, still active', [$project, 'project_days_round1' => $project->days_round1, 'project_days_round2' => $project->days_round2, 'project_percent' => $percent]);
					}

				}
			}

			$processed++;
		}

		if ($processed == 0) {
			$this->info("No projects processed");
			return;
		}

		if (!$update) {
			$this->warning('Dummy execution. No write operations done');
			$output->writeln('<comment>No write operations done. Please execute the command with the --update modifier to perform write operations</comment>');
		}
	}
}
