<?php

namespace App\Command;

use App\Repository\ReviewRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReviewStatusUpdate extends Command

{
    /**
     * @var ReviewRepository
     */
    private $reviewRepository;

    public function __construct(string $name = null, ReviewRepository $reviewRepository)
    {
        parent::__construct($name);
        $this->reviewRepository = $reviewRepository;
    }

    protected function configure()
    {
        $this->setName('ReviewStatusUpdate')
            ->setDescription('This command will update the status of the reviews')
            ->setHelp('This command will update the status of the reviews');
        $this->addArgument('status',InputArgument::OPTIONAL,'entre the flag');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status= $input->getArgument('status');
        if ($status === "false") {
            $this->reviewRepository->updateReviewsStatus("Rejected");
        }
        else
        {
            try {
                $this->reviewRepository->updateReviewsStatus("Approved");
            }
            catch (\Exception $exception){
                echo $exception->getMessage();
            }
        }
  }

}