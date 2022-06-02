<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface;

interface ExecutionStrategyInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface
     */
    public function getExecutor(SchedulerJobTransfer $jobTransfer): ExecutorInterface;
}
