<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface;

class ExecutionStrategy implements ExecutionStrategyInterface
{
    private ExecutorInterface $executorForExistingJob;

    private ExecutorInterface $executorForAbsentJob;

    /**
     * @var array<bool>
     */
    private array $jobNames = [];

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface $executorForExistingJob
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface $executorForAbsentJob
     */
    public function __construct(
        ExecutorInterface $executorForExistingJob,
        ExecutorInterface $executorForAbsentJob
    ) {
        $this->executorForExistingJob = $executorForExistingJob;
        $this->executorForAbsentJob = $executorForAbsentJob;
    }

    /**
     * @param string $jobName
     *
     * @return $this
     */
    public function addJobName(string $jobName)
    {
        $this->jobNames[$jobName] = true;

        return $this;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function getExecutor(SchedulerJobTransfer $jobTransfer): ExecutorInterface
    {
        return $this->doesJobExist($jobTransfer)
            ? $this->executorForExistingJob
            : $this->executorForAbsentJob;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return bool
     */
    protected function doesJobExist(SchedulerJobTransfer $jobTransfer): bool
    {
        return $this->jobNames[$jobTransfer->getName()] ?? false;
    }
}
