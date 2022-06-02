<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\TemplateGenerator\KubernetesTemplateGeneratorInterface;

class CreateExecutor implements ExecutorInterface
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
            // continuous jobs are assumed to run endlessly, so do not create a CronJob, instead a long-running Job resource
            $jobTemplate = $this->cronJobTemplateGenerator->generateJobTemplate($jobTransfer);

            return $this->kubernetesApi->createJob($jobName, $jobTemplate);
        }

        return (new SchedulerKubernetesResponseTransfer())->setStatus(true)->setMessage('Ignoring job ' . $jobName);
    }
}
