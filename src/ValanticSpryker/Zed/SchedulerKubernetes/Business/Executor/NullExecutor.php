<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;

class NullExecutor implements ExecutorInterface
{
    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function execute(SchedulerJobTransfer $jobTransfer): SchedulerKubernetesResponseTransfer
    {
        return (new SchedulerKubernetesResponseTransfer())->setStatus(true);
    }
}
