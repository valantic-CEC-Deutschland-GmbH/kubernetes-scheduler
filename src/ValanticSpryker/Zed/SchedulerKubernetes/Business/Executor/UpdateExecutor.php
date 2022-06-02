<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator\KubernetesTemplateGeneratorInterface;

class UpdateExecutor implements ExecutorInterface
{
    private KubernetesApiInterface $kubernetesApi;

    private KubernetesTemplateGeneratorInterface $cronJobTemplateGenerator;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface $kubernetesApi
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator\KubernetesTemplateGeneratorInterface $cronJobTemplateGenerator
     */
    public function __construct(
        KubernetesApiInterface $kubernetesApi,
        KubernetesTemplateGeneratorInterface $cronJobTemplateGenerator
    ) {
        $this->kubernetesApi = $kubernetesApi;
        $this->cronJobTemplateGenerator = $cronJobTemplateGenerator;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer
     */
    public function execute(SchedulerJobTransfer $jobTransfer): SchedulerKubernetesResponseTransfer
    {
        $jobName = $jobTransfer->requireName()->getName();

        if (($jobTransfer->getPayload()[SchedulerJobTransfer::CONTINUOUS] ?? false) === true) {
            // Jobs can't be updated, so re-create Job
            // https://stackoverflow.com/questions/56336067/updating-a-kubernetes-job-what-happens
            $this->kubernetesApi->deleteJob($jobName);

            $jobTemplate = $this->cronJobTemplateGenerator->generateJobTemplate($jobTransfer);

            return $this->kubernetesApi->createJob($jobName, $jobTemplate);
        }

        return (new SchedulerKubernetesResponseTransfer())->setStatus(true)->setMessage('Ignoring job ' . $jobName);
    }
}
