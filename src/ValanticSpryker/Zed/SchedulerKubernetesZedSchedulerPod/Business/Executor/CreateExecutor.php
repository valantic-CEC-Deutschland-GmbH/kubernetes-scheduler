<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerKubernetesResponseTransfer;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator\CronFileTemplateGeneratorInterface;

class CreateExecutor implements ExecutorInterface
{
    private KubernetesApiInterface $kubernetesApi;

    private CronFileTemplateGeneratorInterface $cronJobTemplateGenerator;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface $kubernetesApi
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\TemplateGenerator\CronFileTemplateGeneratorInterface $cronJobTemplateGenerator
     */
    public function __construct(
        KubernetesApiInterface $kubernetesApi,
        CronFileTemplateGeneratorInterface $cronJobTemplateGenerator
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

        if (($jobTransfer->getPayload()[SchedulerJobTransfer::CONTINUOUS] ?? false) === false) {
            $crontabFileContents = $this->cronJobTemplateGenerator->generateCronFileTemplate($jobTransfer);

            return $this->kubernetesApi->addToConfigMap($jobName, $crontabFileContents);
        }

        return (new SchedulerKubernetesResponseTransfer())->setStatus(true)->setMessage('Ignoring job ' . $jobName);
    }
}
