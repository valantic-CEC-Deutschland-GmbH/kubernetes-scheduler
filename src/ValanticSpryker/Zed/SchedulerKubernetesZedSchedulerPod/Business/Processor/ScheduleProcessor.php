<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor;

use Generated\Shared\Transfer\SchedulerJobTransfer;
use Generated\Shared\Transfer\SchedulerResponseTransfer;
use Generated\Shared\Transfer\SchedulerScheduleTransfer;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyBuilderInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig;

class ScheduleProcessor implements ScheduleProcessorInterface
{
    /**
     * @var string
     */
    private const REGEX_INVALID_DNS_CHARS = '/[^-0-9a-zA-Z]/';

    /**
     * @var string
     */
    private const INVALID_DNS_CHARS_REPLACEMENT = '-';

    private ExecutionStrategyBuilderInterface $executionStrategyBuilder;

    private KubernetesApiInterface $kubernetesApi;

    private SchedulerKubernetesZedSchedulerPodConfig $config;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyBuilderInterface $executionStrategyBuilder
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface $kubernetesApi
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\SchedulerKubernetesZedSchedulerPodConfig $config
     */
    public function __construct(
        ExecutionStrategyBuilderInterface $executionStrategyBuilder,
        KubernetesApiInterface $kubernetesApi,
        SchedulerKubernetesZedSchedulerPodConfig $config
    ) {
        $this->executionStrategyBuilder = $executionStrategyBuilder;
        $this->kubernetesApi = $kubernetesApi;
        $this->config = $config;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $scheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    public function processSchedule(SchedulerScheduleTransfer $scheduleTransfer): SchedulerResponseTransfer
    {
        $executionStrategy = $this->executionStrategyBuilder->buildExecutionStrategy();
        $schedulerResponseTransfer = $this->createSchedulerResponseTransfer($scheduleTransfer);

        foreach ($scheduleTransfer->getJobs() as $jobTransfer) {
            $jobTransfer = $this->normalizeJobName($jobTransfer);
            $executor = $executionStrategy->getExecutor($jobTransfer);
            $response = $executor->execute($jobTransfer);

            if ($response->getStatus() === false) {
                return $schedulerResponseTransfer
                    ->setStatus(false)
                    ->setMessage($response->getMessage());
            }
        }

        if ($this->config->restartSchedulerPodOnUpdate()) {
            $this->kubernetesApi->restartScheduler();
        }

        return $schedulerResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerScheduleTransfer $scheduleTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerResponseTransfer
     */
    private function createSchedulerResponseTransfer(SchedulerScheduleTransfer $scheduleTransfer): SchedulerResponseTransfer
    {
        return (new SchedulerResponseTransfer())
            ->setSchedule($scheduleTransfer)
            ->setStatus(true);
    }

    /**
     * @param \Generated\Shared\Transfer\SchedulerJobTransfer $jobTransfer
     *
     * @return \Generated\Shared\Transfer\SchedulerJobTransfer
     */
    private function normalizeJobName(SchedulerJobTransfer $jobTransfer): SchedulerJobTransfer
    {
        // Spryker unfortunately uses a non-DNS-compliant way of generating job names which aren't compatible with Kubernetes,
        // so normalize them here
        $jobName = strtolower($jobTransfer->getName());
        $jobName = preg_replace(self::REGEX_INVALID_DNS_CHARS, self::INVALID_DNS_CHARS_REPLACEMENT, $jobName);
        $jobTransfer->setName(substr($jobName, 0, 52));

        return $jobTransfer;
    }
}
