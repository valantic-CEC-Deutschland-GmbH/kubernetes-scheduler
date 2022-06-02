<?php declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerScheduleTransfer;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Exception\InconsistentJobsDetectedException;

class InconsistentJobRemover implements InconsistentJobRemoverInterface
{
    private KubernetesApiInterface $kubernetesApi;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface $kubernetesApi
     */
    public function __construct(KubernetesApiInterface $kubernetesApi)
    {
        $this->kubernetesApi = $kubernetesApi;
    }

    /**
     * @inheritDoc
     *
     * @throws \ValanticSpryker\Zed\SchedulerKubernetes\Business\Exception\InconsistentJobsDetectedException
     */
    public function remove(SchedulerScheduleTransfer $scheduleTransfer): void
    {
        $cronJobs = json_decode($this->kubernetesApi->getCronJobs()->getPayload(), true)['jobs'];
        $jobs = json_decode($this->kubernetesApi->getJobs()->getPayload(), true)['jobs'];

        $success = true;
        foreach ($scheduleTransfer->getJobs() as $jobTransfer) {
            $shouldRunContinuously = $jobTransfer->getPayload()[SchedulerJobTransfer::CONTINUOUS] ?? false;
            if ($shouldRunContinuously === true && isset($cronJobs[$jobTransfer->getName()])) {
                $success &= $this->kubernetesApi->deleteCronJob($jobTransfer->getName())->getStatus();
                $success &= $this->kubernetesApi->stopExecution($jobTransfer->getName())->getStatus();
            } elseif ($shouldRunContinuously === false && isset($jobs[$jobTransfer->getName()])) {
                $success &= $this->kubernetesApi->stopExecution($jobTransfer->getName())->getStatus();
            }
        }

        if (!$success) {
            throw new InconsistentJobsDetectedException('Inconsistent jobs could not be removed. Please check manually.');
        }
    }
}
