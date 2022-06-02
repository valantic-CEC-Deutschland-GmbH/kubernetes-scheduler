<?php
declare(strict_types = 1);

namespace ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy;

use ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface;
use ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface;

class ExecutionStrategyBuilder implements ExecutionStrategyBuilderInterface
{
    /**
     * @var string
     */
    private const KEY_JOBS = 'jobs';

    /**
     * @var string
     */
    private const KEY_NAME = 'name';

    private KubernetesApiInterface $kubernetesApi;

    private ExecutorInterface $executorForExistingJob;

    private ExecutorInterface $executorForAbsentJob;

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Api\KubernetesApiInterface $kubernetesApi
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface $executorForExistingJob
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Executor\ExecutorInterface $executorForAbsentJob
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
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategyInterface
     */
    public function buildExecutionStrategy(): ExecutionStrategyInterface
    {
        $jobs = $this->getJobs();
        $executionStrategy = new ExecutionStrategy($this->executorForExistingJob, $this->executorForAbsentJob);

        if (!is_array($jobs)) {
            return $executionStrategy;
        }

        return $this->mapJobCheckerFromArray($executionStrategy, $jobs[static::KEY_JOBS] ?? []);
    }

    /**
     * @param \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategy $jobChecker
     * @param array $jobs
     *
     * @return \ValanticSpryker\Zed\SchedulerKubernetes\Business\Processor\Strategy\ExecutionStrategy
     */
    private function mapJobCheckerFromArray(ExecutionStrategy $jobChecker, array $jobs): ExecutionStrategy
    {
        foreach ($jobs as $job) {
            if (is_array($job) && array_key_exists(static::KEY_NAME, $job)) {
                $jobChecker->addJobName($job[static::KEY_NAME]);
            }
        }

        return $jobChecker;
    }

    /**
     * @return mixed|null
     */
    private function getJobs()
    {
        $response = $this->kubernetesApi->getAllJobs();

        if ($response->getStatus() === false) {
            return null;
        }

        return json_decode($response->getPayload(), true);
    }
}
