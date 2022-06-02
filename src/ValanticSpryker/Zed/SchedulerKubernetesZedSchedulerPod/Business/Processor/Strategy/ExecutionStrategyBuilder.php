<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy;

use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApi;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface;

class ExecutionStrategyBuilder implements ExecutionStrategyBuilderInterface
{
    private KubernetesApiInterface $kubernetesApi;

    private ExecutorInterface $executorForExistingJob;

    private ExecutorInterface $executorForAbsentJob;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Api\KubernetesApiInterface $kubernetesApi
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface $executorForExistingJob
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Executor\ExecutorInterface $executorForAbsentJob
     */
    public function __construct(
        KubernetesApiInterface $kubernetesApi,
        ExecutorInterface $executorForExistingJob,
        ExecutorInterface $executorForAbsentJob
    ) {
        $this->kubernetesApi = $kubernetesApi;
        $this->executorForExistingJob = $executorForExistingJob;
        $this->executorForAbsentJob = $executorForAbsentJob;
    }

    /**
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategyInterface
     */
    public function buildExecutionStrategy(): ExecutionStrategyInterface
    {
        $jobs = $this->getJobs();
        $executionStrategy = new ExecutionStrategy($this->executorForExistingJob, $this->executorForAbsentJob);

        if (!is_array($jobs)) {
            return $executionStrategy;
        }

        return $this->mapJobCheckerFromArray($executionStrategy, $jobs ?? []);
    }

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategy $jobChecker
     * @param array $jobs
     *
     * @return \ValanticSpryker\Zed\SchedulerKubernetesZedSchedulerPod\Business\Processor\Strategy\ExecutionStrategy
     */
    private function mapJobCheckerFromArray(ExecutionStrategy $jobChecker, array $jobs): ExecutionStrategy
    {
        foreach ($jobs as $job) {
            if (is_array($job) && array_key_exists(KubernetesApi::INDEX_NAME, $job)) {
                $jobChecker->addJobName($job[KubernetesApi::INDEX_NAME]);
            }
        }

        return $jobChecker;
    }

    /**
     * @return array
     */
    private function getJobs(): array
    {
        $response = $this->kubernetesApi->getJobList();
        if ($response->getStatus() === false) {
            return [];
        }

        return json_decode($response->getPayload(), true);
    }
}
