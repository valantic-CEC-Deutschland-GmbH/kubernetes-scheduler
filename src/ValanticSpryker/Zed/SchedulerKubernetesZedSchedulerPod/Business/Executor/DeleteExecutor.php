<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface;

class DeleteExecutor implements ExecutorInterface
{
    private KubernetesApiInterface $kubernetesApi;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface $kubernetesApi
     */
    public function __construct(KubernetesApiInterface $kubernetesApi)
    {
        $this->kubernetesApi = $kubernetesApi;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function execute(SchedulerJobTransfer $jobTransfer): SchedulerKubernetesResponseTransfer
    {
        $jobName = $jobTransfer->requireName()->getName();

        if (($jobTransfer->getPayload()[SchedulerJobTransfer::CONTINUOUS] ?? false) === false) {
            return $this->kubernetesApi->removeFromConfigMap($jobName);
        }

        return (new SchedulerKubernetesResponseTransfer())->setStatus(true)->setMessage('Ignoring job ' . $jobName);
    }
}
